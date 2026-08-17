<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-xl">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">URL Shortener</h1>
            <p class="text-gray-600">Paste your long URL below and get a short link instantly.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form id="shorten-form" class="space-y-4">
                @csrf
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700 mb-1">Long URL <span class="text-red-500">*</span></label>
                    <input
                        type="url"
                        id="url"
                        name="url"
                        placeholder="https://example.com/very-long-url"
                        required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <p id="url-error" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="alias" class="block text-sm font-medium text-gray-700 mb-1">Custom Alias <span class="text-gray-400">(optional)</span></label>
                        <input
                            type="text"
                            id="alias"
                            name="alias"
                            placeholder="my-link"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        <p id="alias-error" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>
                    <div>
                        <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">Expires <span class="text-gray-400">(optional)</span></label>
                        <input
                            type="datetime-local"
                            id="expires_at"
                            name="expires_at"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        <p id="expires-error" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-gray-400">(optional)</span></label>
                    <input
                        type="text"
                        id="password"
                        name="password"
                        placeholder="Leave empty for public link"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    <p id="password-error" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>
                <button
                    type="submit"
                    id="submit-btn"
                    class="w-full bg-indigo-600 text-white font-medium py-2.5 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span id="btn-text">Shorten URL</span>
                    <svg id="btn-spinner" class="hidden animate-spin ml-2 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>

            <div id="result" class="hidden mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm font-medium text-green-800 mb-2">Short URL created</p>
                <div class="flex items-center gap-2 mb-3">
                    <input
                        type="text"
                        id="short-url-input"
                        readonly
                        class="flex-1 rounded-md border-green-300 bg-white text-sm text-gray-700 shadow-sm"
                    >
                    <button
                        type="button"
                        id="copy-btn"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    >
                        Copy
                    </button>
                </div>
                <div class="flex items-center gap-2 mb-3">
                    <button
                        type="button"
                        id="qr-btn"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        QR Code
                    </button>
                </div>
                <div id="qr-container" class="hidden flex justify-center mb-3">
                    <img id="qr-image" src="" alt="QR Code" class="border border-gray-200 rounded-lg">
                </div>
                <a id="short-url-link" href="#" target="_blank" class="inline-block text-sm text-green-700 hover:text-green-800 underline"></a>
            </div>

            <div id="error-box" class="hidden mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm font-medium text-red-800">Something went wrong. Please try again.</p>
            </div>
        </div>

        <p class="text-center text-sm text-gray-500 mt-6">Fast, free, and simple URL shortening.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const form = document.getElementById('shorten-form');
        const urlInput = document.getElementById('url');
        const aliasInput = document.getElementById('alias');
        const expiresInput = document.getElementById('expires_at');
        const passwordInput = document.getElementById('password');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');
        const resultBox = document.getElementById('result');
        const shortUrlInput = document.getElementById('short-url-input');
        const shortUrlLink = document.getElementById('short-url-link');
        const copyBtn = document.getElementById('copy-btn');
        const errorBox = document.getElementById('error-box');
        const urlError = document.getElementById('url-error');
        const aliasError = document.getElementById('alias-error');
        const expiresError = document.getElementById('expires-error');
        const passwordError = document.getElementById('password-error');

        function setLoading(loading) {
            submitBtn.disabled = loading;
            btnText.textContent = loading ? 'Shortening...' : 'Shorten URL';
            btnSpinner.classList.toggle('hidden', !loading);
        }

        function showResult(shortUrl) {
            shortUrlInput.value = shortUrl;
            shortUrlLink.href = shortUrl;
            shortUrlLink.textContent = 'Open shortened URL';
            resultBox.classList.remove('hidden');
            errorBox.classList.add('hidden');
            qrContainer.classList.add('hidden');
            qrBtn.textContent = 'QR Code';
        }

        function showError(message) {
            errorBox.querySelector('p').textContent = message || 'Something went wrong. Please try again.';
            errorBox.classList.remove('hidden');
            resultBox.classList.add('hidden');
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            urlError.classList.add('hidden');
            aliasError.classList.add('hidden');
            expiresError.classList.add('hidden');
            passwordError.classList.add('hidden');
            errorBox.classList.add('hidden');
            resultBox.classList.add('hidden');

            const url = urlInput.value.trim();
            const alias = aliasInput.value.trim();
            const expiresAt = expiresInput.value;
            const password = passwordInput.value.trim();

            if (!url) {
                urlError.textContent = 'Please enter a URL.';
                urlError.classList.remove('hidden');
                return;
            }

            setLoading(true);

            try {
                const { data } = await axios.post('/shorten', { url, alias, expires_at: expiresAt, password });
                showResult(data.short_url);
                urlInput.value = '';
                aliasInput.value = '';
                expiresInput.value = '';
                passwordInput.value = '';
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    if (errors && errors.url) {
                        urlError.textContent = errors.url[0];
                        urlError.classList.remove('hidden');
                    }
                    if (errors && errors.alias) {
                        aliasError.textContent = errors.alias[0];
                        aliasError.classList.remove('hidden');
                    }
                    if (errors && errors.expires_at) {
                        expiresError.textContent = errors.expires_at[0];
                        expiresError.classList.remove('hidden');
                    }
                    if (errors && errors.password) {
                        passwordError.textContent = errors.password[0];
                        passwordError.classList.remove('hidden');
                    }
                    if (!errors || Object.keys(errors).length === 0) {
                        showError('Please check your input and try again.');
                    }
                } else {
                    showError();
                }
            } finally {
                setLoading(false);
            }
        });

        copyBtn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(shortUrlInput.value);
                const originalText = copyBtn.textContent;
                copyBtn.textContent = 'Copied!';
                setTimeout(() => {
                    copyBtn.textContent = originalText;
                }, 2000);
            } catch (err) {
                shortUrlInput.select();
                document.execCommand('copy');
                copyBtn.textContent = 'Copied!';
                setTimeout(() => {
                    copyBtn.textContent = 'Copy';
                }, 2000);
            }
        });

        const qrBtn = document.getElementById('qr-btn');
        const qrContainer = document.getElementById('qr-container');
        const qrImage = document.getElementById('qr-image');

        qrBtn.addEventListener('click', () => {
            const shortCode = shortUrlInput.value.replace(window.location.origin, '').replace(/^\//, '');
            if (!shortCode) return;

            qrImage.src = `/qr/${shortCode}`;
            qrContainer.classList.remove('hidden');
            qrBtn.textContent = 'Refresh QR';
        });
    </script>
</body>
</html>
