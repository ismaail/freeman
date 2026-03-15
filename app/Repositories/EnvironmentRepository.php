<?php

namespace App\Repositories;

use App\Models\Environment;
use App\Models\EnvironmentVariable;
use Illuminate\Database\Eloquent\Collection;

class EnvironmentRepository
{
    public function allForUser(int $userId): Collection
    {
        return Environment::where('user_id', $userId)
            ->with('variables')
            ->orderBy('name')
            ->get();
    }

    public function findForUser(int $id, int $userId): Environment
    {
        return Environment::where('id', $id)
            ->where('user_id', $userId)
            ->with('variables')
            ->firstOrFail();
    }

    public function create(int $userId, array $data): Environment
    {
        return Environment::create([
            'user_id'   => $userId,
            'name'      => $data['name'],
            'is_active' => false,
        ]);
    }

    public function update(Environment $environment, array $data): Environment
    {
        $environment->update(['name' => $data['name']]);

        return $environment->fresh('variables');
    }

    public function delete(Environment $environment): void
    {
        $environment->delete();
    }

    /**
     * Set this environment as the active one for its user — deactivate all others.
     */
    public function activate(Environment $environment): Environment
    {
        Environment::where('user_id', $environment->user_id)
            ->update(['is_active' => false]);

        $environment->update(['is_active' => true]);

        return $environment->fresh('variables');
    }

    /**
     * Deactivate all environments for a user (no active environment).
     */
    public function deactivateAll(int $userId): void
    {
        Environment::where('user_id', $userId)->update(['is_active' => false]);
    }

    /**
     * Return the active environment for a user, or null if none is active.
     */
    public function findActive(int $userId): ?Environment
    {
        return Environment::where('user_id', $userId)
            ->where('is_active', true)
            ->with('variables')
            ->first();
    }

    // --- Variable methods ---

    public function syncVariables(Environment $environment, array $variables): Environment
    {
        $environment->variables()->delete();

        foreach ($variables as $var) {
            if (isset($var['key']) && $var['key'] !== '') {
                EnvironmentVariable::create([
                    'environment_id' => $environment->id,
                    'key'            => $var['key'],
                    'value'          => $var['value'] ?? '',
                    'enabled'        => $var['enabled'] ?? true,
                ]);
            }
        }

        return $environment->fresh('variables');
    }
}
