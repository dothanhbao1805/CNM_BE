<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;

interface CategoryRepositoryInterface
{
    public function getAll();
    public function findById(int $id): ?Category;
    public function create(array $data): Category;
    public function update(int $id, array $data): Category;
    public function delete(int $id): bool;
    public function restore(int $id): bool;
    public function getTrashed();
}
