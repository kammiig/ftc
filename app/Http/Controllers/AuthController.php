<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt(array_merge($credentials, ['status' => 'active']), $remember)) {
            $request->session()->regenerate();
            ActivityLog::record('login', 'User logged in.');

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials or inactive user account.'])
            ->onlyInput('email');
    }

    public function destroy(Request $request): RedirectResponse
    {
        ActivityLog::record('logout', 'User logged out.');
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
