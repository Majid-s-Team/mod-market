<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;






class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'platform_id',
        'platform_type',
        'device_type',
        'device_token',
        'latitude',
        'longitude',
        'contact_number',
        'password',
        'is_term_accept',
        'business_license_image',
        'id_card_number',
        'address',
        'cover_photo',
        'street',
        'city',
        'state',
        'service_rate',
        'certificate',
        'profile_image',
        'otp',
        'otp_expire_at',
        'role',
        'fcm_token',
        'gateway_customer_id',
        'gateway_connect_id',
        'gateway_charges_enabled',
        'gateway_payouts_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp'
    ];

    protected $casts = [
        'otp_expire_at' => 'datetime',
        'is_term_accept' => 'boolean',
        'service_rate' => 'decimal:2'
    ];
    protected $appends = ['average_rating', 'reviews_count'];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function vehicleAds()
    {
        return $this->hasMany(VehicleAd::class);
    }

    public function availabilities()
    {
        return $this->hasMany(InspectorAvailability::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class, 'user_id');
    }
    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewed_id');
    }

    public function getAverageRatingAttribute()
    {
        return round($this->reviewsReceived()->avg('rating') ?? 0, 2);
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviewsReceived()->count();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public static function socialUser(array $params)
{
    // 1️⃣ Check user by platform info
    $user = User::where('platform_type', $params['platform_type'] ?? null)
        ->where('platform_id', $params['platform_id'] ?? null)
        ->first();

        // dd(vars: $user);

    // If not found, check by email (for Google usually)
    if (!$user && !empty($params['email'])) {
        $user = User::where('email', $params['email'])
            ->first();
    }

    //  Handle image (optional)
    // $uploadedImagePath = null;
    // if (!empty($params['image_url'])) {
    //     try {
    //         $imageContent = @file_get_contents($params['image_url']);
    //         if ($imageContent) {
    //             $filename = 'users/' . uniqid('social_') . '.jpg';
    //             Storage::disk('public')->put($filename, $imageContent);
    //             $uploadedImagePath = 'storage/' . $filename;
    //         }
    //     } catch (\Exception $e) {
    //         // Ignore image download failures silently
    //     }
    // }

    // 4️⃣ Create new user if not found
    if (!$user) {
        $password = Str::random(10);

        // dd($params['device_token']);

        $user = User::create([
            'role'   => $params['role'],
            'name'      => $params['name'],
            'email'           => $params['email'] ?? null,
            'password'        => Hash::make($password),
            'contact_no'       => $params['contact_no'] ?? null,
            'profile_image'       => $params['profile_image'],
            'platform_type'   => $params['platform_type'],
            'platform_id'     => $params['platform_id'],
            'device_type'     => $params['device_type'] ?? null,
            'device_token'    => $params['device_token'] ?? null,
            'is_term_accept'   => $params['is_term_accept'],
            'created_at'      => Carbon::now(),
        ]);
// dd($user);

    }
    else {
        // 5️⃣ Update user fields if already exists
        $updateData = [
            'name'         => $params['name'] ?? $user->name,
            'email'        => $params['email'] ?? $user->email,
            'profile_image'    => $params['profile_image'] ?? $user->profile_image,
            'device_type'  => $params['device_type'] ?? $user->device_type,
            'device_token' => $params['device_token'] ?? $user->device_token,
            'updated_at'   => Carbon::now(),
        ];

        $user->update($updateData);
    }

    // 6️⃣ Return minimal object
    return (object) [
        'id'          => $user->id,
        'created_at'  => $user->created_at,
    ];
}

}
