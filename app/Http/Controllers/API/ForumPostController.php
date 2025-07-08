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
        $posts = ForumPost::with(['attachments', 'likes', 'comments'])->latest()->paginate($perPage);

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

        if (!empty($data['attachments'])) {
            $post->attachments()->delete();

            foreach ($data['attachments'] as $url) {
                $path = str_replace(asset('storage') . '/', '', $url);
                $post->attachments()->create(['file_url' => $path]);
            }
        }


        // return response()->json($post->load('attachments'));
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