<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\Interfaces\CategoryServiceInterface;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryServiceInterface $categoryService
    ) {
    }

    public function index()
    {
        return CategoryResource::collection(
            $this->categoryService->getAll()
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->create($request->validated());
        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, int $id)
    {
        $result = $this->categoryService->update($id, $request->validated());
        return response()->json([
            'message' => 'Updated successfully',
            'data' => $result
        ]);
    }

    public function destroy(int $id)
    {
        $this->categoryService->delete($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function trashed()
    {
        return CategoryResource::collection(
            $this->categoryService->getTrashed()
        );
    }


    public function restore(int $id)
    {
        $this->categoryService->restore($id);

        return response()->json([
            'message' => 'Restore successfully'
        ]);
    }
}
