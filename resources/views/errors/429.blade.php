@include('errors.layout', [
    'code'    => 429,
    'title'   => 'Too Many Requests',
    'message' => 'You are sending requests too quickly. Please wait a moment and try again.',
])
