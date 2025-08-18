<?php

namespace App\Http\Controllers\API;

use App\Models\SubCategory;
use App\Models\VehicleAd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;

class SubCategoryController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $subcategories = SubCategory::with('category')->get();
        return $this->apiResponse('SubCategories fetched successfully.', $subcategories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id'
        ]);

        $subCategory = SubCategory::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'is_active' => true
        ]);

        return $this->apiResponse('SubCategory created successfully.', $subCategory->toArray(), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id'
        ]);

        $subCategory = SubCategory::findOrFail($id);
        $subCategory->update($request->only(['name', 'category_id']));

        return $this->apiResponse('SubCategory updated successfully.', $subCategory->toArray());
    }

    public function changeStatus($id)
    {
        $subCategory = SubCategory::findOrFail($id);
        $subCategory->is_active = !$subCategory->is_active;
        $subCategory->save();

        return $this->apiResponse('SubCategory status changed.', $subCategory->toArray());
    }


    public function getByCategory($categoryId)
    {
        $subcategories = SubCategory::where('category_id', $categoryId)->get();
        return $this->apiResponse('SubCategories fetched by category.', $subcategories->toArray());
    }

    public function getVehiclesByCategoryAndSubcategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id'
        ]);

        $vehicles = VehicleAd::where('category_id', $request->category_id)
            ->where('sub_category_id', $request->sub_category_id)
            ->with((new VehicleAd())->getAllRelations())
            ->get();

        return $this->apiResponse('Vehicles fetched by category and subcategory.', $vehicles->toArray());
    }

}
