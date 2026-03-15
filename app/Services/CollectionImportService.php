<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\CollectionFolder;
use App\Models\Request as ApiRequest;
use Illuminate\Support\Arr;

/**
 * Imports a Postman Collection v2.1 JSON file (DD-007).
 *
 * Handles:
 *  - Collection name + description
 *  - Nested folders (recursive)
 *  - Requests with headers, body (raw / form-data / urlencoded), and auth
 */
class CollectionImportService
{
    /**
     * Parse a decoded Postman v2.1 array and persist everything for $userId.
     *
     * @param  array  $data  Decoded JSON from the uploaded file
     * @throws \InvalidArgumentException when the file is not a recognised Postman v2.1 collection
     */
    public function import(array $data, int $userId): Collection
    {
        $this->validate($data);

        $info = $data['info'];

        $collection = Collection::create([
            'user_id'     => $userId,
            'name'        => $info['name'] ?? 'Imported Collection',
            'description' => $info['description'] ?? null,
        ]);

        $items = $data['item'] ?? [];
        $this->processItems($items, $collection->id, $userId, null);

        return $collection;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function validate(array $data): void
    {
        $schema = $data['info']['schema'] ?? '';

        if (! str_contains($schema, 'v2.1') && ! str_contains($schema, 'v2.0')) {
            if (empty($data['info']['name']) || ! isset($data['item'])) {
                throw new \InvalidArgumentException(
                    'File does not appear to be a valid Postman Collection (v2.0 or v2.1).'
                );
            }
        }
    }

    /**
     * Recursively process an array of Postman items (folders or requests).
     */
    private function processItems(array $items, int $collectionId, int $userId, ?int $parentFolderId): void
    {
        foreach ($items as $item) {
            if ($this->isFolder($item)) {
                $folder = CollectionFolder::create([
                    'collection_id'    => $collectionId,
                    'parent_folder_id' => $parentFolderId,
                    'name'             => $item['name'] ?? 'Unnamed Folder',
                ]);

                // Recurse into sub-items
                $this->processItems($item['item'] ?? [], $collectionId, $userId, $folder->id);
            } else {
                $this->createRequest($item, $collectionId, $userId, $parentFolderId);
            }
        }
    }

    /**
     * A Postman item is a folder when it has an "item" array child (even if empty).
     */
    private function isFolder(array $item): bool
    {
        return isset($item['item']) && is_array($item['item']);
    }

    /**
     * Map a Postman request item to a Request model and persist it.
     */
    private function createRequest(array $item, int $collectionId, int $userId, ?int $folderId): void
    {
        $req = $item['request'] ?? [];

        // Method
        $method = strtoupper($req['method'] ?? 'GET');
        if (! in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $method = 'GET';
        }

        // URL — can be a string or a {raw, ...} object
        $url = '';
        if (isset($req['url'])) {
            $url = is_string($req['url']) ? $req['url'] : ($req['url']['raw'] ?? '');
        }

        // Headers
        $headers = $this->parseHeaders($req['header'] ?? []);

        // Body
        [$bodyType, $body] = $this->parseBody($req['body'] ?? null);

        // Auth
        [$authType, $authData] = $this->parseAuth($req['auth'] ?? null);

        ApiRequest::create([
            'collection_id' => $collectionId,
            'folder_id'     => $folderId,
            'user_id'       => $userId,
            'name'          => $item['name'] ?? 'Untitled Request',
            'method'        => $method,
            'url'           => $url,
            'headers'       => $headers,
            'body_type'     => $bodyType,
            'body'          => $body,
            'auth_type'     => $authType,
            'auth_data'     => $authData,
        ]);
    }

    /**
     * Convert Postman header array → our {key, value, enabled} format.
     */
    private function parseHeaders(array $rawHeaders): array
    {
        $out = [];
        foreach ($rawHeaders as $h) {
            $key = $h['key'] ?? '';
            if ($key === '') {
                continue;
            }
            $out[] = [
                'key'     => $key,
                'value'   => $h['value'] ?? '',
                'enabled' => ! ($h['disabled'] ?? false),
            ];
        }

        return $out;
    }

    /**
     * Convert a Postman body object → [body_type, body_string].
     *
     * @return array{0: string, 1: string|null}
     */
    private function parseBody(?array $rawBody): array
    {
        if (empty($rawBody)) {
            return ['none', null];
        }

        $mode = $rawBody['mode'] ?? 'none';

        if ($mode === 'raw') {
            return ['raw', $rawBody['raw'] ?? ''];
        }

        if ($mode === 'formdata') {
            $rows = $this->parseFormRows($rawBody['formdata'] ?? []);

            return ['form-data', json_encode($rows)];
        }

        if ($mode === 'urlencoded') {
            $rows = $this->parseFormRows($rawBody['urlencoded'] ?? []);

            return ['x-www-form-urlencoded', json_encode($rows)];
        }

        return ['none', null];
    }

    /**
     * Convert Postman form rows → our {key, value, enabled} format.
     */
    private function parseFormRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $key = $row['key'] ?? '';
            if ($key === '') {
                continue;
            }
            $out[] = [
                'key'     => $key,
                'value'   => $row['value'] ?? '',
                'enabled' => ! ($row['disabled'] ?? false),
            ];
        }

        return $out;
    }

    /**
     * Convert a Postman auth object → [auth_type, auth_data_array].
     *
     * @return array{0: string, 1: array}
     */
    private function parseAuth(?array $rawAuth): array
    {
        if (empty($rawAuth)) {
            return ['none', []];
        }

        $type = strtolower($rawAuth['type'] ?? 'none');

        if ($type === 'bearer') {
            $kvList = $rawAuth['bearer'] ?? [];
            $kv     = $this->kvListToMap($kvList);

            return ['bearer', ['token' => $kv['token'] ?? '']];
        }

        if ($type === 'basic') {
            $kvList = $rawAuth['basic'] ?? [];
            $kv     = $this->kvListToMap($kvList);

            return ['basic', [
                'username' => $kv['username'] ?? '',
                'password' => $kv['password'] ?? '',
            ]];
        }

        if ($type === 'apikey') {
            $kvList = $rawAuth['apikey'] ?? [];
            $kv     = $this->kvListToMap($kvList);

            return ['api_key', [
                'key'   => $kv['key']   ?? '',
                'value' => $kv['value'] ?? '',
                'in'    => $kv['in']    ?? 'header',
            ]];
        }

        return ['none', []];
    }

    /**
     * Postman stores auth params as [{key, value, type}, ...].
     * Convert to a simple associative map for easy lookup.
     */
    private function kvListToMap(array $list): array
    {
        $map = [];
        foreach ($list as $entry) {
            if (isset($entry['key'])) {
                $map[$entry['key']] = $entry['value'] ?? '';
            }
        }

        return $map;
    }
}
