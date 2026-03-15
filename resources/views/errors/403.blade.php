@include('errors.layout', [
    'code'    => 403,
    'title'   => 'Access Denied',
    'message' => $exception->getMessage() ?: 'You do not have permission to access this page.',
])
