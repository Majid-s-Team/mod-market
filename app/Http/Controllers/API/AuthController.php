<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\RegisterInspectorRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\ApiResponseTrait;


class AuthController extends Controller
{
    use ApiResponseTrait;

    public function registerUser(RegisterUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'latitude'=>$request->latitude,
            'longitude'=>$request->longitude,
            'contact_number' => $request->contact_number,
            'password' => Hash::make($request->password),
            'is_term_accept' => $request->is_term_accept,
            'address' => $request->address ?? null,
            'cover_photo' => $request->cover_photo_url ?? null,
            'role' => 'user',
            'fcm_token' => $request->fcm_token ?? null,
        ]);

        $user->assignRole('user');
        $token = JWTAuth::fromUser($user);

        return $this->apiResponse('User registered successfully', [
            'user' => $user->fresh(),
            // 'roles' => $user->getRoleNames(),
            'access_token' => $token

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
        if ($request->filled('cover_photo_url')) {
            $user->cover_photo = $request->cover_photo_url;
        }
        if ($request->filled('address')) {
            $user->address = $request->address;
        }


        $user->save();
        $user->assignRole('inspector');
        $token = JWTAuth::fromUser($user);

        return $this->apiResponse('Inspector registered successfully', [
            // 'roles' => $user->getRoleNames(),
            'access_token' => $token,
              'user' => $user->fresh(),
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
            return response()->json(['error' => 'Invalid credentials'], 422);
        }

        $token = JWTAuth::fromUser($user);

        return $this->apiResponse('Login successful', [
            'access_token' => $token,
            'user' => $user,

        ]);
    }

       public function socialLogin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'platform_id'   => 'required|string|max:255',
        'platform_type' => 'required|in:facebook,google,apple',
        'device_type'   => 'required|in:android,ios,web',
        'device_token'  => 'nullable|string',
        'name'    => 'required|string',
        'role' => 'required|in:user,inspector',
        'contact_no'   => 'nullable|string',
        'email'         => 'nullable|email',
        'profile_image'     => 'nullable|url',
        'is_term_accept' => 'required|boolean'

    ]);

    if ($validator->fails()) {
        return $this->sendError('Validation Error', $validator->errors()->all(), 422);
    }

    $params = $validator->validated();

    // Try finding existing user
    $user = User::where('platform_type', $params['platform_type'])
        ->where('platform_id', $params['platform_id'])
        ->whereNull('deleted_at')
        ->first();

        // dd(vars: $user);

    // Apple case: no email provided
    if (empty($user) && empty($params['email'])&& $params['device_type']=='ios') {
        return $this->sendError(
            'The current information is incomplete. Please go to Settings > Apple ID > Password & Security > Sign in with Apple, remove the app, and sign in again.',
            [],
            400
        );
    }

    // Create or update user
    $userData = User::socialUser($params);
    $user = User::find($userData->id);

    // Check if user is inactive
    if (!$user->is_active) {
        return $this->sendError('Account is deactivated. Please contact support.', [], 403);
    }

    // Generate new API token
    $user->tokens()->delete(); // optional: remove old tokens
    $token = $user->createToken('API Token')->plainTextToken;

    // Update device_token if provided
    if (!empty($params['device_token'])) {
        $user->update(['device_token' => $params['device_token']]);
    }

    return $this->sendResponse('Social login successful', [
        'token' => $token,
        'user'  => $user,
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

        return $this->apiResponse('OTP sent successfully', [
            'otp' => $otp,
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required']);

        $user = User::where('email', $request->email)->where('otp', $request->otp)->first();
        if (!$user || $user->otp_expire_at < now()) {
            return response()->json(['error' => 'Invalid or expired OTP'], 422);
        }

        return $this->apiResponse('OTP verified successfully', [
            'email' => $user->email,
            'name' => $user->name,

        ]);
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

        return $this->apiResponse('Password reset successfully');
    }

    public function profile()
    {
        return $this->apiResponse('User profile fetched successfully', [
            'user' => auth()->user()
        ]);
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
        if ($request->filled('cover_photo_url')) {
            $user->cover_photo = $request->cover_photo_url;
        }

        if ($request->filled('address')) {
            $user->address = $request->address;
        }

        $user->save();

        return $this->apiResponse('Profile updated successfully', [
            'user' => $user
        ]);
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

        return $this->apiResponse('Password changed successfully');
    }
 public function getUsers(Request $request, $id = null)
    {
        if ($id) {
            $user = User::where('role', 'user')->findOrFail($id);
            return $this->apiResponse('User detail fetched successfully.', $user);
        }

        $perPage = $request->get('per_page', 10);

        $query = User::where('role', 'user');


        $users = $query->paginate($perPage);

        return $this->apiPaginatedResponse('Users list fetched successfully.', $users);
    }


}
