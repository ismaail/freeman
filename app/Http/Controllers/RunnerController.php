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
        $result = $this->runner->run(
            method:    $request->input('method'),
            url:       $request->input('url'),
            headers:   $request->input('headers', []),
            body:      $request->input('body'),
            bodyType:  $request->input('body_type'),
            authType:  $request->input('auth_type'),
            authData:  $request->input('auth_data'),
            userId:    auth()->id(),
            requestId: $request->input('request_id'),
        );

        return response()->json($result);
    }
}
