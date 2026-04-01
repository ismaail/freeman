<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\CollectionFolder;
use App\Models\Request as ApiRequest;
use App\Repositories\CollectionRepository;

/**
 * Exports a collection to Postman Collection v2.1 JSON format (DD-007).
 *
 * Spec reference: https://schema.postman.com/collection/json/v2.1.0/draft-07/collection.json
 */
class CollectionExportService
{
    public function __construct(private CollectionRepository $repo) {}

    /**
     * Returns the Postman v2.1 array structure for the given collection.
     */
    public function export(int $collectionId): array
    {
        $collection = $this->repo->withTree($collectionId);

        return [
            'info' => [
                '_postman_id' => (string) $collection->id,
                'name'        => $collection->name,
                'description' => $collection->description ?? '',
                'schema'      => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => $this->buildItems($collection),
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Build the top-level item array: root folders + root requests.
     */
    private function buildItems(Collection $collection): array
    {
        $items = [];

        // Top-level folders (no parent)
        $rootFolders = $collection->folders->whereNull('parent_folder_id');
        foreach ($rootFolders as $folder) {
            $items[] = $this->buildFolder($folder, $collection->folders->all());
        }

        // Requests sitting directly on the collection (no folder)
        foreach ($collection->requests as $request) {
            $items[] = $this->buildRequest($request);
        }

        return $items;
    }

    /**
     * Recursively build a folder item (Postman "item" with sub-items).
     *
     * @param  CollectionFolder[]  $allFolders  Flat list of all folders for this collection
     */
    private function buildFolder(CollectionFolder $folder, array $allFolders): array
    {
        $subItems = [];

        // Child folders
        foreach ($allFolders as $candidate) {
            if ((int) $candidate->parent_folder_id === (int) $folder->id) {
                $subItems[] = $this->buildFolder($candidate, $allFolders);
            }
        }

        // Requests in this folder
        foreach ($folder->requests as $request) {
            $subItems[] = $this->buildRequest($request);
        }

        return [
            'name' => $folder->name,
            'item' => $subItems,
        ];
    }

    /**
     * Convert a Request model to a Postman item object.
     */
    private function buildRequest(ApiRequest $req): array
    {
        return [
            'name'    => $req->name,
            'request' => [
                'method' => $req->method,
                'header' => $this->buildHeaders($req->headers ?? []),
                'body'   => $this->buildBody($req),
                'url'    => $this->buildUrl($req->url),
                'auth'   => $this->buildAuth($req),
            ],
            'response' => [],
        ];
    }

    /**
     * Map our {key, value, enabled} header array to Postman header format.
     */
    private function buildHeaders(array $headers): array
    {
        $out = [];
        foreach ($headers as $h) {
            if (empty($h['key'])) {
                continue;
            }
            $out[] = [
                'key'      => $h['key'],
                'value'    => $h['value'] ?? '',
                'disabled' => isset($h['enabled']) ? ! $h['enabled'] : false,
            ];
        }

        return $out;
    }

    /**
     * Build the Postman body object from a request.
     */
    private function buildBody(ApiRequest $req): array
    {
        $bodyType = $req->body_type ?? 'none';

        if ($bodyType === 'none' || $bodyType === null) {
            return ['mode' => 'none'];
        }

        if ($bodyType === 'raw') {
            return [
                'mode' => 'raw',
                'raw'  => $req->body ?? '',
                'options' => ['raw' => ['language' => 'json']],
            ];
        }

        if ($bodyType === 'form-data') {
            return [
                'mode'     => 'formdata',
                'formdata' => $this->buildFormRows($req->body ?? ''),
            ];
        }

        if ($bodyType === 'x-www-form-urlencoded') {
            return [
                'mode'       => 'urlencoded',
                'urlencoded' => $this->buildFormRows($req->body ?? ''),
            ];
        }

        return ['mode' => 'none'];
    }

    /**
     * Decode JSON-encoded form rows and map to Postman key/value format.
     */
    private function buildFormRows(string $body): array
    {
        $rows = json_decode($body, true);
        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (empty($row['key'])) {
                continue;
            }
            $out[] = [
                'key'      => $row['key'],
                'value'    => $row['value'] ?? '',
                'disabled' => isset($row['enabled']) ? ! $row['enabled'] : false,
            ];
        }

        return $out;
    }

    /**
     * Build a Postman URL object.  For simplicity we store as a raw string;
     * Postman accepts both string and object forms.
     */
    private function buildUrl(string $url): array
    {
        return [
            'raw'  => $url,
            'host' => [preg_replace('#^https?://#', '', $url)],
        ];
    }

    /**
     * Build the Postman auth object, or null when auth_type is none.
     */
    private function buildAuth(ApiRequest $req): ?array
    {
        $type = $req->auth_type ?? 'none';
        $data = $req->auth_data ?? [];

        if ($type === 'none') {
            return null;
        }

        if ($type === 'bearer') {
            return [
                'type'   => 'bearer',
                'bearer' => [
                    ['key' => 'token', 'value' => $data['token'] ?? '', 'type' => 'string'],
                ],
            ];
        }

        if ($type === 'basic') {
            return [
                'type'  => 'basic',
                'basic' => [
                    ['key' => 'username', 'value' => $data['username'] ?? '', 'type' => 'string'],
                    ['key' => 'password', 'value' => $data['password'] ?? '', 'type' => 'string'],
                ],
            ];
        }

        if ($type === 'api_key') {
            return [
                'type'   => 'apikey',
                'apikey' => [
                    ['key' => 'key',   'value' => $data['key']   ?? '', 'type' => 'string'],
                    ['key' => 'value', 'value' => $data['value'] ?? '', 'type' => 'string'],
                    ['key' => 'in',    'value' => $data['in']    ?? 'header', 'type' => 'string'],
                ],
            ];
        }

        return null;
    }
}
