@extends('portal.layout')

@section('title', 'Login | NotifyHub Portal')

@section('content')
    <div class="card" style="max-width: 460px; margin: 80px auto;">
        <h1 style="margin-top: 0;">Login</h1>
        <p class="muted">Sign in with a NotifyHub user account to inspect project events.</p>

        @if ($errors->any())
            <div class="status error">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route('portal.login.store') }}" class="grid">
            @csrf
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            </div>

            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>

            <label style="display:flex; align-items:center; gap: 8px; margin-bottom:0;">
                <input type="checkbox" name="remember" value="1" style="width:auto;">
                Remember me
            </label>

            <button type="submit" class="btn">Sign in</button>
        </form>
    </div>
@endsection

