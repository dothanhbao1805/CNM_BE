<?php

namespace App\Services\Interfaces;

interface BrandServiceInterface
{
    public function getAll();
    public function restore(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function getTrashed();
}
