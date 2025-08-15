<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Models\ForumPost;
use Illuminate\Support\Facades\Storage;

class ForumPostController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $authUserId = auth()->id();

        $posts = ForumPost::with([
            'user:id,name,profile_image',
            'attachments',
            'likes.user:id,name,profile_image',
            'comments' => function ($query) {
                $query->with([
                    'user:id,name,profile_image',
                    'reactions.user:id,name,profile_image',
                    'replies' => function ($replyQuery) {
                        $replyQuery->with([
                            'user:id,name,profile_image',
                            'reactions.user:id,name,profile_image'
                        ]);
                    }
                ])->whereNull('parent_id');
            }
        ])
            ->latest()
            ->paginate($perPage);

        $posts->getCollection()->transform(function ($post) use ($authUserId) {
            // post level flags
            $post->is_liked = $post->likes->contains('user_id', $authUserId);
            $post->is_commented = $post->comments->contains('user_id', $authUserId);

            // comment level flags
            $post->comments->transform(function ($comment) use ($authUserId) {
                $comment->is_commented = ($comment->user_id === $authUserId);
                $comment->is_reacted = $comment->reactions->contains('user_id', $authUserId);

                // reply level flags
                $comment->replies->transform(function ($reply) use ($authUserId) {
                    $reply->is_commented = ($reply->user_id === $authUserId);
                    $reply->is_reacted = $reply->reactions->contains('user_id', $authUserId);
                    return $reply;
                });

                return $comment;
            });

            return $post;
        });

        return $this->apiPaginatedResponse('Forum posts fetched successfully', $posts);
    }



    public function store(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|string',
            'privacy' => 'in:public,private',
            'attachments' => 'nullable|array',
            'attachments.*' => 'url'
        ]);

        $post = ForumPost::create([
            'user_id' => auth()->id(),
            'description' => $data['description'],
            'privacy' => $data['privacy'] ?? 'public',
            'is_draft' => false,
        ]);

        foreach ($data['attachments'] as $url) {
            $path = str_replace(asset('storage') . '/', '', $url);
            $post->attachments()->create(['file_url' => $path]);
        }


        // return response()->json($post->load('attachments'), 201);
        return $this->apiResponse('Forum post created successfully', [
            'post' => $post->load('attachments')
        ], 201);
    }

    // public function show($id)
    // {
    //     return ForumPost::with(['attachments', 'likes', 'comments.replies', 'comments.reactions', 'user'])->findOrFail($id);
    // }
    public function show($id)
    {
        $post = ForumPost::with([
            'attachments',
            'likes',
            'comments.replies',
            'comments.reactions',
            'user'
        ])->find($id);

        if (!$post) {
            return $this->apiError('Post not found', [], 404);
        }

        return $this->apiResponse('Forum post fetched successfully', [
            'post' => $post
        ]);
    }

    public function update(Request $request, $id)
    {
        $post = ForumPost::findOrFail($id);
        abort_if($post->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'description' => 'required|string',
            'privacy' => 'in:public,private',
            'attachments' => 'nullable|array',
            'attachments.*' => 'url'
        ]);

        $post->update($data);


        $post->attachments()->delete();

        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as $url) {
                $path = str_replace(asset('storage') . '/', '', $url);
                $post->attachments()->create(['file_url' => $path]);
            }
        }

        return $this->apiResponse('Forum post updated successfully', [
            'post' => $post->load('attachments')
        ]);
    }

    public function destroy($id)
    {
        $post = ForumPost::findOrFail($id);
        abort_if($post->user_id !== auth()->id(), 403);

        $post->attachments()->delete();
        $post->delete();

        // return response()->json(['message' => 'Post deleted successfully']);
        return $this->apiResponse('Forum post deleted successfully');

    }

    public function toggleDraft($id)
    {
        $post = ForumPost::findOrFail($id);
        abort_if($post->user_id !== auth()->id(), 403);

        $post->is_draft = !$post->is_draft;
        $post->save();

        // return response()->json(['draft' => $post->is_draft]);
        return $this->apiResponse('Draft status toggled', [
            'is_draft' => $post->is_draft
        ]);
    }
}