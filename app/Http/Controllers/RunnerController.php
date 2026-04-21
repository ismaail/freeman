<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunRequestRequest;
use App\Services\RequestRunnerService;
use Illuminate\Http\JsonResponse;

class RunnerController extends Controller
{
    public function __construct(private RequestRunnerService $runner) {}

    public function run(RunRequestRequest $request): JsonResponse
    {
        // When the request is sent as multipart/form-data (file uploads), complex fields
        // arrive as JSON strings — decode them back to arrays.
        $headers  = $request->input('headers', []);
        $authData = $request->input('auth_data');

        if (is_string($headers)) {
            $headers = json_decode($headers, true) ?? [];
        }
        if (is_string($authData)) {
            $authData = json_decode($authData, true);
        }

        $result = $this->runner->run(
            method:        $request->input('method'),
            url:           $request->input('url'),
            headers:       $headers,
            body:          $request->input('body'),
            bodyType:      $request->input('body_type'),
            authType:      $request->input('auth_type'),
            authData:      $authData,
            userId:        auth()->id(),
            requestId:     $request->input('request_id') ?: null,
            environmentId: $request->input('environment_id'),
            collectionId:  $request->input('collection_id') ?: null,
            bodyFormRows:  $request->input('body_form', []),
            bodyFormFiles: $request->file('body_form_files', []),
        );

        return response()->json($result);
    }
}
