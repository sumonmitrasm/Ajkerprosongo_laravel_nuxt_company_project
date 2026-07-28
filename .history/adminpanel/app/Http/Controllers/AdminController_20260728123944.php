<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
            if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
                $seconds = RateLimiter::availableIn($throttleKey);
                return response()->json([
                    'status' => false,
                    'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
                ], 429);
            }
            $data = $request->only('email', 'password');
            $validator = Validator::make($data, [
                'email' => ['required', 'email', 'max:40'],
                'password' => ['required', 'string', 'max:20'],
            ], [
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'password.required' => 'Password is required.',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            if (Auth::guard('admin')->attempt([
                'email' => $data['email'],
                'password' => $data['password'],
                'status' => 1,
            ])) {
                RateLimiter::clear($throttleKey);
                $request->session()->regenerate();
                return response()->json([
                    'status' => true,
                    'message' => 'Login successful.',
                    'redirect_url' => route('admin.dashboard'),
                ]);
            }
            RateLimiter::hit($throttleKey, 300);
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password.',
            ], 422);
        }

        return view('admin.login');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('admin/login');
    }

    public function users(Request $request){
        $title = "Admin Users";
        return view('admin.accounts.admin-user')->with(compact('title'));
    }
}
