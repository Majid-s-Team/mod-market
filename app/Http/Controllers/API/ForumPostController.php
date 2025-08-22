<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Models\ForumPost;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;


class ForumPostController extends Controller
{
    use ApiResponseTrait;

  public function index(Request $request)
{
    $authUserId = null;

    // if request has bearer token
    if ($request->bearerToken()) {
        try {
            // authenticate user through bearer
            $user = auth('api')->user();
            if (!$user) {
             // Token is invalid or expired (returned null)
             throw new AuthenticationException('Unauthenticated. Invalid or expired token.');
            }
                $authUserId = $user->id;

        } catch (AuthenticationException $e) {
            //token un-authorized exception
            return response()->json([
                'message' => $e-> getMessage()], 401);
        }
    }
    // if bearer token is not present then -> authUserId null

    $perPage = $request->get('per_page', 10);
    $search = $request->input('search'); // ✅ get search term

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
   ->when($search, function ($query, $search) {
        // ✅ apply filter if search term is present
        $query->where('description', 'like', '%' . $search . '%');
    })
    ->latest()
    ->paginate($perPage);

    $posts->getCollection()->transform(function ($post) use ($authUserId) {
        $post->is_liked = $authUserId ? $post->likes->contains('user_id', $authUserId) : false;
        $post->is_commented = $authUserId ? $post->comments->contains('user_id', $authUserId) : false;

        $post->comments->transform(function ($comment) use ($authUserId) {
            $comment->is_commented = ($authUserId && $comment->user_id === $authUserId);
            $comment->is_reacted = $authUserId ? $comment->reactions->contains('user_id', $authUserId) : false;

            $comment->replies->transform(function ($reply) use ($authUserId) {
                $reply->is_commented = ($authUserId && $reply->user_id === $authUserId);
                $reply->is_reacted = $authUserId ? $reply->reactions->contains('user_id', $authUserId) : false;

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
   public function trendingForumPost(Request $request)
{
    // Get pagination inputs (default: 10 per page)
    $perPage = $request->input('per_page', default: 1);
    $page = $request->input('page', 1);

    // Get authenticated user (if needed for flags like is_liked later)
    $authUserId = auth()->id();

    // Fetch forum posts with full relationship data
    $posts = ForumPost::withCount(['likes', 'comments'])
        ->with([
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
        ->orderByDesc(DB::raw('likes_count + comments_count')) // Sort by engagement
        ->paginate($perPage, ['*'], 'page', $page);

    // Add dynamic flags (like is_liked, is_commented) if needed
    $posts->getCollection()->transform(function ($post) use ($authUserId) {
        $post->is_liked = $authUserId ? $post->likes->contains('user_id', $authUserId) : false;
        $post->is_commented = $authUserId ? $post->comments->contains('user_id', $authUserId) : false;

        $post->comments->transform(function ($comment) use ($authUserId) {
            $comment->is_commented = $authUserId && $comment->user_id === $authUserId;
            $comment->is_reacted = $authUserId ? $comment->reactions->contains('user_id', $authUserId) : false;

            $comment->replies->transform(function ($reply) use ($authUserId) {
                $reply->is_commented = $authUserId && $reply->user_id === $authUserId;
                $reply->is_reacted = $authUserId ? $reply->reactions->contains('user_id', $authUserId) : false;
                return $reply;
            });

            return $comment;
        });

        return $post;
    });

    return $this->apiPaginatedResponse('Trending forum posts fetched successfully', $posts, 200);
}

}
