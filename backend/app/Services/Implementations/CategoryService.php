<?php

namespace App\Services\Implementations;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\Interfaces\CategoryServiceInterface;
use Illuminate\Support\Str;

class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepo
    ) {
    }

    public function getAll()
    {
        return $this->categoryRepo->getAll();
    }

    public function create(array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        return $this->categoryRepo->create($data);
    }

    public function getTrashed()
    {
        return $this->categoryRepo->getTrashed();
    }


    public function update(int $id, array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        return $this->categoryRepo->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->categoryRepo->delete($id);
    }

    public function restore(int $id)
    {
        return $this->categoryRepo->restore($id);
    }

}
