<?php

namespace App\Repositories\Interfaces;

use App\Models\Brand;

interface BrandRepositoryInterface
{
    public function getAll();
    public function findById(int $id): ?Brand;
    public function create(array $data): Brand;
    public function update(int $id, array $data): Brand;
    public function delete(int $id): bool;
    public function restore(int $id): bool;
    public function getTrashed();
}
