<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | Task Manager</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
    <main class="content login-content">
        <div class="card login-card">
            <h1>Log In</h1>

            @if (session('error'))
                <x-alert type="error">{{ session('error') }}</x-alert>
            @endif

            @if (session('success'))
                <x-alert>{{ session('success') }}</x-alert>
            @endif

            @if (auth()->check())
                <p>Signed in as {{ auth()->user()->email }}.</p>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-secondary" type="submit">Log Out</button>
                </form>
            @endif

            <form action="{{ route('login.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email"
                        value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" id="password" name="password" type="password" required>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                </div>

                <button class="btn btn-primary" type="submit">Log In</button>
                <a href="{{ route('register') }}" class="btn btn-secondary">Create an account</a>
            </form>
        </div>
    </main>
</body>

</html>
