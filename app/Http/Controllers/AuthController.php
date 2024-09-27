<?php
namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Mail\LoginNotification;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
    }

    // Handle the login process
    public function login(Request $request)
    {
        $this->validateLogin($request);

        $user = $this->getUserByEmail($request->input('email'));

        if ($this->verifyAccount($user, $request->input('password'), $request)) {
            return redirect()->intended('/'); // Redirect to intended page or localhost/
        }

        return redirect()->back()->withErrors(['error' => 'Je e-mailadres of wachtwoord is onjuist!']);
    }

    private function validateLogin(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);
    }

    private function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    private function verifyAccount($user, $password, Request $request)
    {
        if ($user && Hash::check($password, $user->password)) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                return redirect()->back()->withErrors(['error' => 'Als leerling kan je niet inloggen op een applicatie voor docenten.']);
            }
            if ((config('auth.account_activation') && $user->activation_code !== 'activated') ||
                (config('auth.account_approval') && !$user->approved)) {
                return redirect()->back()->withErrors(['error' => 'Activeer of keur je account goed om in te loggen!']);
            }
            Auth::login($user);
            $this->handleRememberMe($user, $request);
            $user->update(['last_seen' => now()]);
            return true;
        }
        return false;
    }

    private function handleRememberMe($user, Request $request)
    {
        if ($request->filled('remember_me')) {
            $cookieHash = $user->remember_me_code ?: Hash::make($user->id . $user->username . config('app.key'));
            $days = 30;
            cookie()->queue('remember_me', $cookieHash, $days * 1440); // 1440 minutes per day
            $user->update(['remember_me_code' => $cookieHash]);
        }
    }

    // Show the forgot password form
    public function showForgotPasswordForm()
    {
        return Auth::check() ? redirect()->route('dashboard') : view('auth.passwords.email');
    }

    // Send reset link
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = $this->getUserByEmail($request->input('email'));

        if ($user) {
            $status = Password::sendResetLink($request->only('email'));

            return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
        }

        return back()->withErrors(['email' => __('We hebben geen account gevonden met dit e-mailadres.')]);
    }

    // Show the reset password form
    public function showResetForm(Request $request, $token = null)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if (!$token) {
            return redirect()->route('password.request');
        }

        return view('auth.passwords.reset')->with(['token' => $token, 'email' => $request->email]);
    }

    // Handle the reset password logic
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                Auth::login($user);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
