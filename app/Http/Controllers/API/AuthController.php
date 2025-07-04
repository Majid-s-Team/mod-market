<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\RegisterInspectorRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function registerUser(RegisterUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'password' => Hash::make($request->password),
            'is_term_accept' => $request->is_term_accept,
            'role' => 'user',
        ]);

        $user->assignRole('user');

        // return response()->json(['message' => 'User registered successfully']);
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'roles' => $user->getRoleNames()
        ]);
    }

    public function registerInspector(RegisterInspectorRequest $request)
    {
        $user = new User($request->all());
        $user->password = Hash::make($request->password);
        $user->role = 'inspector';

        if ($request->hasFile('business_license_image')) {
            $user->business_license_image = $request->file('business_license_image')->store('uploads', 'public');
        }
        if ($request->hasFile('certificate')) {
            $user->certificate = $request->file('certificate')->store('uploads', 'public');
        }
        if ($request->hasFile('profile_image')) {
            $user->profile_image = $request->file('profile_image')->store('uploads', 'public');
        }

        $user->save();
        $user->assignRole('inspector');

        return response()->json([
            'message' => 'Inspector registered successfully',
            'user' => $user,
            'roles' => $user->getRoleNames()
        ]);

    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required'
        ]);

        $loginInput = $request->login;
        $password = $request->password;


        $user = User::where('email', $loginInput)
            ->orWhere('contact_number', $loginInput)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user)
            return response()->json(['error' => 'User not found'], 404);

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expire_at = now()->addMinutes(10);
        $user->save();

        return response()->json(['message' => 'OTP sent', 'otp' => $otp]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required']);

        $user = User::where('email', $request->email)->where('otp', $request->otp)->first();
        if (!$user || $user->otp_expire_at < now()) {
            return response()->json(['error' => 'Invalid or expired OTP'], 422);
        }

        return response()->json(['message' => 'OTP verified']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|confirmed'
        ]);

        $user = User::where('email', $request->email)->where('otp', $request->otp)->first();
        if (!$user)
            return response()->json(['error' => 'Invalid OTP'], 422);

        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->otp_expire_at = null;
        $user->save();

        return response()->json(['message' => 'Password reset successfully']);
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        $user->update($request->except('password', 'profile_image'));

        if ($request->hasFile('profile_image')) {
            $user->profile_image = $request->file('profile_image')->store('uploads/profiles', 'public');
        } elseif ($request->filled('profile_image_url')) {
            $user->profile_image = $request->profile_image_url;
        }

        $user->save();

        return response()->json(['message' => 'Profile updated', 'user' => $user]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed|min:6'
        ]);

        $user = auth()->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'Old password is incorrect'], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully']);
    }


}