<?php

namespace App\Repositories\Eloquent;

use App\Models\Brand;
use App\Repositories\Interfaces\BrandRepositoryInterface;

class BrandRepository implements BrandRepositoryInterface
{
    public function getAll()
    {
        return Brand::all();
    }

    public function getTrashed()
    {
        return Brand::onlyTrashed()->get();
    }


    public function findById(int $id): ?Brand
    {
        return Brand::find($id);
    }

    public function create(array $data): Brand
    {
        return Brand::create($data);
    }

    public function update(int $id, array $data): Brand
    {
        $brand = Brand::findOrFail($id);
        $brand -> update($data);

        return $brand;
    }

    public function delete(int $id): bool
    {
        return Brand::where('id', $id)->delete();
    }

    public function restore(int $id): bool
    {
        return Brand::withTrashed()
            ->where('id', $id)
            ->restore();
    }
}
