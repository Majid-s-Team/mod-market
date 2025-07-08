<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaticPage;
use App\Traits\ApiResponseTrait;

class StaticPageController extends Controller
{
    use ApiResponseTrait;

    // Public: Get page by slug
    public function show($slug)
    {
        $page = StaticPage::where('slug', $slug)->first();

        if (!$page) {
            return $this->apiError('Page not found', [], 404);
        }

        return $this->apiResponse('Page fetched successfully', [
            'title' => $page->title,
            'content' => $page->content
        ]);
    }

    // Admin: Update page
    public function update(Request $request, $slug)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string'
        ]);

        $page = StaticPage::where('slug', $slug)->first();

        if (!$page) {
            return $this->apiError('Page not found', [], 404);
        }

        $page->update([
            'title' => $request->title,
            'content' => $request->content
        ]);

        return $this->apiResponse('Page updated successfully', [
            'title' => $page->title,
            'content' => $page->content
        ]);
    }
}
