<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function getAll();
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);  
    public function delete($id);
    public function findByEmail(string $email);
    public function updatePassword(string $userId, string $password): bool;
    public function emailExists(string $email): bool;
    public function findBySocialProvider(string $provider, string $providerId);
    

}
