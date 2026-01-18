<?php

namespace App\Repositories\Interfaces;

interface DiscountRepositoryInterface
{
    public function create(array $data);
    public function findByCode($code);
}