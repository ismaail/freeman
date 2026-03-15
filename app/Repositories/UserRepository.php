<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function allNonAdmin(): Collection
    {
        return User::where('is_super_admin', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findOrFail(int $id): User
    {
        return User::findOrFail($id);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
