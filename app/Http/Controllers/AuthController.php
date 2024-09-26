<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Mail\LoginNotification;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        if (Auth::check()) {
            // Redirect to the home page if the user is already logged in
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    // Handle the login process
    // Handle the login process
    public function login(Request $request)
    {
        // Validate the input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        // Attempt to find the user by email
        $user = User::where('email', $request->input('email'))->first();

        if ($user && Hash::check($request->input('password'), $user->password)) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                return redirect()->back()->withErrors(['error' => 'Als leerling kan je niet inloggen op een applicatie voor docenten.']);
            }
            // Check if the account is activated
            if (config('auth.account_activation') && $user->activation_code !== 'activated') {
                return redirect()->back()->withErrors(['error' => 'Activeer je account om in te loggen!']);
            } elseif (1 + 1 == 3) {
                return redirect()->route('maintenance');
            } elseif (config('auth.account_approval') && !$user->approved) {
                return redirect()->back()->withErrors(['error' => 'Je account is nog niet goedgekeurd!']);
            } else {
                // Log the user in
                Auth::login($user);

                $user = Auth::user();
                $ipAddress = $request->ip();
                $time = now()->toDateTimeString();


                // Handle "remember me" functionality
                if ($request->filled('remember_me')) {
                    $cookie_hash = $user->remember_me_code ?: Hash::make($user->id . $user->username . config('app.key'));
                    $days = 30;
                    cookie()->queue('remember_me', $cookie_hash, $days * 1440); // 1440 minutes per day
                    $user->update(['remember_me_code' => $cookie_hash]);
                }

                // Update last seen date
                $user->update(['last_seen' => now()]);

                // Redirect to the dashboard
                return redirect()->intended('/'); // this will redirect to the intended page or localhost/
            }
        }
        return redirect()->back()->withErrors(['error' => 'Je e-mailadres of wachtwoord is onjuist!']);
    }

    public function showForgotPasswordForm()
    {
        if (Auth::check()) {
            // Redirect to the home page if the user is already logged in
            return redirect()->route('dashboard');
        }
        return view('auth.passwords.email');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->input('email'))->first();

        if ($user) {
            // Gebruik de standaard Laravel methode om een token te genereren en op te slaan
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return back()->with(['status' => __($status)]);
            }

            return back()->withErrors(['email' => __($status)]);
        } else {
            return back()->withErrors(['email' => __('We hebben geen account gevonden met dit e-mailadres.')]);
        }
    }


    // Show the reset password form
    public function showResetForm(Request $request, $token = null)
    {
        if (Auth::check()) {
            // Redirect to the home page if the user is already logged in
            return redirect()->route('home');
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
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                Auth::login($user);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }


    public function showBlocked()
    {
        return view('auth.blocked');
    }

    public function showMaintenance()
    {
        return view('auth.maintenance');
    }
}
