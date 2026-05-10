@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-8">
        <div class="text-center">
            <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                <svg class="h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v-2m0 2H4m16 0h-4m-8-4h4m-4 0h4" />
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                @if($statusCode === 404)
                    Page Not Found
                @elseif($statusCode === 403)
                    Access Denied
                @elseif($statusCode === 401)
                    Unauthorized
                @elseif($statusCode === 500)
                    Server Error
                @elseif($statusCode === 503)
                    Service Unavailable
                @else
                    Error ({{ $statusCode }})
                @endif
            </h1>

            <p class="text-gray-600 text-lg mb-4">
                {{ $message }}
            </p>

            @if(config('app.debug'))
                <div class="mt-4 p-4 bg-gray-100 rounded text-left text-sm text-gray-700">
                    <strong>Error Code:</strong> {{ $errorCode }}
                </div>
            @endif

            <div class="mt-6 flex gap-4 justify-center">
                <a href="{{ route('home') }}" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition">
                    Go Home
                </a>
                <button onclick="history.back()" class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded hover:bg-gray-400 transition">
                    Go Back
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
