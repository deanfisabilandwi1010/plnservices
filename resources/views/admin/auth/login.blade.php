<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Masuk — Portal Admin PLN Services</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    @fonts
    @vite(['resources/css/admin.css'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
    <main class="w-full max-w-[400px]">
        <div class="mb-8 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="PLN Services" class="mx-auto h-12 w-auto">
            <p class="mt-4 text-sm text-muted">Portal Admin</p>
        </div>

        <form method="post" action="{{ route('admin.login') }}" class="card p-7">
            @csrf
            <h1 class="text-lg font-bold">Masuk ke akun Anda</h1>

            @error('email')
                <p class="mt-4 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-[13px] text-rose-700" role="alert">{{ $message }}</p>
            @enderror

            <div class="mt-5 space-y-4">
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="input" required autofocus autocomplete="username">
                </div>
                <div>
                    <label for="password" class="label">Password</label>
                    <input id="password" type="password" name="password" class="input" required autocomplete="current-password">
                </div>
                <label class="flex items-center gap-2 text-[13px] text-muted">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-line accent-[#3498db]"> Ingat saya
                </label>
                <button class="btn btn-primary w-full py-2.5">Masuk</button>
            </div>
        </form>

        <p class="mt-6 text-center text-[13px] text-muted">
            <a href="{{ route('home') }}" class="hover:text-primary">Kembali ke website</a>
            <span class="mx-2">·</span>© {{ now()->year }} {{ setting('company_name', 'PT PLN Services') }}
        </p>
    </main>
</body>
</html>
