<?php

namespace App\Services;

use App\Models\Environment;
use App\Repositories\EnvironmentRepository;
use Illuminate\Database\Eloquent\Collection;

class EnvironmentService
{
    public function __construct(private EnvironmentRepository $repo) {}

    public function listForUser(int $userId): Collection
    {
        return $this->repo->allForUser($userId);
    }

    public function create(int $userId, array $data): Environment
    {
        return $this->repo->create($userId, $data);
    }

    public function update(int $id, int $userId, array $data): Environment
    {
        $environment = $this->repo->findForUser($id, $userId);
        $updated     = $this->repo->update($environment, $data);

        if (isset($data['variables'])) {
            $updated = $this->repo->syncVariables($updated, $data['variables']);
        }

        return $updated;
    }

    public function delete(int $id, int $userId): void
    {
        $environment = $this->repo->findForUser($id, $userId);
        $this->repo->delete($environment);
    }

    /**
     * Make this environment active; deactivates all others for the user.
     */
    public function activate(int $id, int $userId): Environment
    {
        $environment = $this->repo->findForUser($id, $userId);

        return $this->repo->activate($environment);
    }

    /**
     * Deactivate all environments for the user (back to "no environment").
     */
    public function deactivate(int $userId): void
    {
        $this->repo->deactivateAll($userId);
    }

    /**
     * Returns a flat key → value map of enabled variables for the user's active environment.
     * Returns an empty array when no environment is active.
     */
    public function getActiveVariables(int $userId): array
    {
        $environment = $this->repo->findActive($userId);

        if ($environment === null) {
            return [];
        }

        $vars = [];

        foreach ($environment->variables as $variable) {
            if ($variable->enabled) {
                $vars[$variable->key] = $variable->value;
            }
        }

        return $vars;
    }

    /**
     * Replace {{KEY}} placeholders in $text with values from $variables.
     * Unknown placeholders are left unchanged.
     */
    public function substitute(string $text, array $variables): string
    {
        if (empty($variables)) {
            return $text;
        }

        return preg_replace_callback('/\{\{([^}]+)\}\}/', function (array $matches) use ($variables): string {
            $key = trim($matches[1]);

            return array_key_exists($key, $variables) ? $variables[$key] : $matches[0];
        }, $text);
    }
}
