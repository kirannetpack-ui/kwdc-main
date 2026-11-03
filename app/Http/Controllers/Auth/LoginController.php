<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm()
    {
        Log::info('Login form displayed');
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        Log::info('Login attempt started', [
            'ip' => $request->ip(),
        ]);

        $this->validateLogin($request);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            Log::info('Login successful', [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
            ]);
            return $this->sendLoginResponse($request);
        }

        $attemptedUser = \App\Models\User::where($this->username(), $request->input($this->username()))->first();
        if ($attemptedUser && $attemptedUser->isBanned() && \Illuminate\Support\Facades\Hash::check($request->input('password'), $attemptedUser->password)) {
            $this->incrementLoginAttempts($request);
            Log::warning('Login blocked: account banned', ['user_id' => $attemptedUser->id, 'ip' => $request->ip()]);
            return redirect()->back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'This account has been suspended: ' . ($attemptedUser->ban_reason ?: 'Security compliance review.'),
                ]);
        }

        $this->incrementLoginAttempts($request);
        Log::warning('Login failed', ['ip' => $request->ip()]);

        return $this->sendFailedLoginResponse($request);
    }

    protected function credentials(Request $request)
    {
        return array_merge($request->only($this->username(), 'password'), [
            'is_active' => true,
            'is_banned' => false,
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->isBanned()) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'This account has been suspended: ' . ($user->ban_reason ?: 'Security compliance violation.')]);
        }

        if (!$user->hasVerifiedEmail()) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('activation.notice', ['email' => $user->email])
                ->with('status', 'Please activate your account before signing in.');
        }

        return null;
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        Log::notice('Login failed response sent', ['ip' => $request->ip()]);
        return redirect()->back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'These credentials do not match our records.',
            ]);
    }

    public function logout(Request $request)
    {
        Log::info('User logged out', ['user_id' => Auth::id()]);
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
