<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(private UserRepository $users) {}

    public function index(): View
    {
        return view('admin.users', [
            'users' => $this->users->allNonAdmin(),
        ]);
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        $this->users->create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'is_super_admin' => false,
            'must_change_password' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', "User \"{$request->username}\" created.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = $this->users->findOrFail($id);

        if ($user->is_super_admin) {
            abort(403, 'Cannot delete a super admin.');
        }

        $username = $user->username;
        $this->users->delete($user);

        return redirect()->route('admin.users.index')
            ->with('status', "User \"{$username}\" deleted.");
    }
}
