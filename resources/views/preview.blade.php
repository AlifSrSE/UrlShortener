<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
            <div class="mb-4">
                <svg class="mx-auto h-12 w-12 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">You are leaving this site</h1>
            <p class="text-gray-600 mb-4">
                You are about to be redirected to an external website. Please review the destination URL before proceeding.
            </p>
            <div class="bg-gray-100 rounded-lg p-3 mb-6">
                <p class="text-sm text-gray-500 mb-1">Destination URL</p>
                <p class="text-sm font-medium text-gray-900 break-all">{{ $shortUrl->original_url }}</p>
            </div>
            <form method="POST" action="{{ route('confirm', $shortUrl->short_code) }}" class="space-y-3">
                @csrf
                <button
                    type="submit"
                    class="w-full bg-indigo-600 text-white font-medium py-2.5 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Continue to Website
                </button>
                <a
                    href="{{ route('home') }}"
                    class="block w-full bg-white text-gray-700 font-medium py-2.5 px-4 rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                >
                    Go Back
                </a>
            </form>
        </div>
        <p class="text-center text-sm text-gray-500 mt-6">Powered by URL Shortener</p>
    </div>
</body>
</html>
