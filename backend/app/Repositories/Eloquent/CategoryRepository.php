<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAll()
    {
        return Category::all();
    }

    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function getTrashed()
    {
        return Category::onlyTrashed()->get();
    }


    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);
        $category->update($data);

        return $category;
    }


    public function delete(int $id): bool
    {
        return Category::where('id', $id)->delete();
    }

    public function restore(int $id): bool
    {
        return Category::withTrashed()
            ->where('id', $id)
            ->restore();
    }
}
