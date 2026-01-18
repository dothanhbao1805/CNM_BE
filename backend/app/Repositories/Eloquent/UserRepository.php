<?php
namespace App\Repositories\Eloquent;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function getAll()
    {
        return User::all();
    }

    public function findById($id)
    {
        return User::find($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update($id, array $data)
    {
        $user = User::findOrFail($id);
        $user->update($data);
        
        return $user->fresh();
    }

    public function delete($id)
    {
        return User::destroy($id);
    }

    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }


    public function updatePassword(string $userId, string $password): bool
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        $user->password = Hash::make($password);
        return $user->save();
    }


    public function emailExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }
    
    public function findBySocialProvider(string $provider, string $providerId)
    {
        return User::where('provider', $provider)
                   ->where('provider_id', $providerId)
                   ->first();
    }
    
}
