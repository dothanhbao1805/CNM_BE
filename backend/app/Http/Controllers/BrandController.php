<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Services\Interfaces\BrandServiceInterface;

class BrandController extends Controller
{
    public function __construct(
        protected BrandServiceInterface $brandService
    ) {
    }

    public function index()
    {
        return BrandResource::collection(
            $this->brandService->getAll()
        );
    }

    public function trashed()
    {
        return BrandResource::collection(
            $this->brandService->getTrashed()
        );
    }


    public function store(StoreBrandRequest $request)
    {
        $brand = $this->brandService->create($request->validated());
        return new BrandResource($brand);
    }

    public function update(UpdateBrandRequest $request, int $id)
    {
        $result = $this->brandService->update($id, $request->validated());
        return response()->json([
            'message' => 'Updated successfully',
            'data' => $result
        ]);
    }

    public function destroy(int $id)
    {
        $this->brandService->delete($id);
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function restore(int $id)
    {
        $this->brandService->restore($id);

        return response()->json([
            'message' => 'Restore successfully'
        ]);
    }
}
