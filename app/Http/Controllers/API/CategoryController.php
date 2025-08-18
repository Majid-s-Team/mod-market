<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $categories = Category::with('subCategories')->get();
        return $this->apiResponse('Categories fetched', $categories->toArray());
    }


    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $category = Category::create(['name' => $request->name, 'is_active' => true]);
        return $this->apiResponse('Category created', $category->toArray());
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->only('name'));
        return $this->apiResponse('Category updated', $category);
    }

    public function destroy($id)
    {
        Category::destroy($id);
        return $this->apiResponse('Category deleted');
    }

    public function changeStatus($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);
        return $this->apiResponse('Status changed', $category);
    }
}
