<?php

namespace App\Services\Implementations;

use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Services\Interfaces\BrandServiceInterface;
use Illuminate\Support\Str;

class BrandService implements BrandServiceInterface
{
    public function __construct(
        protected BrandRepositoryInterface $brandRepo
    ) {
    }

    public function getAll()
    {
        return $this->brandRepo->getAll();
    }

    public function getTrashed()
    {
        return $this->brandRepo->getTrashed();
    }


    public function create(array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        return $this->brandRepo->create($data);
    }

    public function update(int $id, array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        return $this->brandRepo->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->brandRepo->delete($id);
    }

    public function restore(int $id)
    {
        return $this->brandRepo->restore($id);
    }

}
