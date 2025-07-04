<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ForumPost;
use Illuminate\Support\Facades\Storage;

class ForumPostController extends Controller
{
    public function index()
    {
        return ForumPost::with(['attachments', 'likes', 'comments'])->latest()->paginate(10);
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


        return response()->json($post->load('attachments'), 201);
    }

    public function show($id)
    {
        return ForumPost::with(['attachments', 'likes', 'comments.replies', 'comments.reactions', 'user'])->findOrFail($id);
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


        return response()->json($post->load('attachments'));
    }

    public function destroy($id)
    {
        $post = ForumPost::findOrFail($id);
        abort_if($post->user_id !== auth()->id(), 403);

        $post->attachments()->delete();
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function toggleDraft($id)
    {
        $post = ForumPost::findOrFail($id);
        abort_if($post->user_id !== auth()->id(), 403);

        $post->is_draft = !$post->is_draft;
        $post->save();

        return response()->json(['draft' => $post->is_draft]);
    }
}