@php
    $code = '403';
    $title = 'Access is not permitted';
    $message = $exception->getMessage() ?: 'Your account does not have permission to open this protected CLPMIS resource.';
@endphp
@include('errors.layout')
