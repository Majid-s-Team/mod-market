<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaticPage;

class StaticPageController extends Controller
{
    // Public: Get page by slug
    public function show($slug)
    {
        $page = StaticPage::where('slug', $slug)->first();

        if (!$page) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        return response()->json([
            'title' => $page->title,
            'content' => $page->content
        ]);
    }

    // Admin: Update page (optional)
    public function update(Request $request, $slug)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string'
        ]);

        $page = StaticPage::where('slug', $slug)->first();

        if (!$page) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        $page->update([
            'title' => $request->title,
            'content' => $request->content
        ]);

        return response()->json(['message' => 'Page updated successfully']);
    }
}
