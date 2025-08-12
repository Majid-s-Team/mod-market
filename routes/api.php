<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ImageUploadController;
use App\Http\Controllers\API\VehicleAdController;
use App\Http\Controllers\API\StaticPageController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\EventAttachmentController;
use App\Http\Controllers\API\ForumPostController;
use App\Http\Controllers\API\ForumLikeController;
use App\Http\Controllers\API\ForumAttachmentController;
use App\Http\Controllers\API\ForumCommentController;
use App\Http\Controllers\API\ForumReactionController;
use App\Http\Controllers\API\VehicleDropdownController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubCategoryController;
use App\Http\Controllers\Api\InspectionRequestController;
use App\Http\Controllers\API\CardController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// User Registration and Login
Route::post('register/user', [AuthController::class, 'registerUser']);
Route::post('register/inspector', [AuthController::class, 'registerInspector']);
Route::post('login', [AuthController::class, 'login']);

// Password Recovery Flow
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('event-attachments/upload', [EventAttachmentController::class, 'upload']);

// Temporary Image Upload (used for vehicles and events)
Route::post('/upload-image', [ImageUploadController::class, 'upload']);
Route::get('events', [EventController::class, 'index']);
Route::get('events/{id}', [EventController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication and User Profile
    |--------------------------------------------------------------------------
    */
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('update-profile', [AuthController::class, 'updateProfile']);


    /*
    |--------------------------------------------------------------------------
    | Vehicle Advertisement Management
    |--------------------------------------------------------------------------
    */
    Route::get('vehicle-ads', [VehicleAdController::class, 'index']);                        // List current user's ads
    Route::post('vehicle-ads', [VehicleAdController::class, 'store']);                       // Create a new ad
    Route::get('vehicle-ads/{id}', [VehicleAdController::class, 'show']);                    // Get single ad details
    Route::put('vehicle-ads/{id}', [VehicleAdController::class, 'update']);                  // Update an ad
    Route::delete('vehicle-ads/{id}', [VehicleAdController::class, 'destroy']);              // Delete an ad
    Route::patch('vehicle-ads/{id}/change-status', [VehicleAdController::class, 'changeStatus']); // Change ad status

    // Vehicle Attachments (media upload)
    Route::post('vehicle-attachments/upload-temp', [VehicleAdController::class, 'uploadTempAttachment']);
    Route::delete('vehicle-ads/{id}/attachments/{attachmentId}', [VehicleAdController::class, 'deleteAttachment']);

    // Publicly visible vehicle ads
    Route::get('public-vehicle-ads', [VehicleAdController::class, 'publicVehicleAds']);

    /*
    |--------------------------------------------------------------------------
    | Event Management & Interest System
    |--------------------------------------------------------------------------
    */
    Route::post('events', [EventController::class, 'store']);                      // Create new event

    Route::put('events/{id}', [EventController::class, 'update']);                // Update event
    Route::delete('events/{id}', [EventController::class, 'destroy']);            // Delete event
    Route::patch('events/{id}/change-status', [EventController::class, 'changeStatus']); // Change event status

    // Filter events by time
    Route::get('events-upcoming', [EventController::class, 'upcoming']);          // Get upcoming events
    Route::get('events-past', [EventController::class, 'past']);                  // Get past events

    // Mark/Unmark interest in event
    Route::post('events/{id}/interest', [EventController::class, 'markInterest']);

    // Event Attachments (media)
    Route::delete('event-attachments/{id}', [EventAttachmentController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Static Pages (About, Terms, Privacy)
    |--------------------------------------------------------------------------
    */
    Route::get('pages/{slug}', [StaticPageController::class, 'show']);   // Retrieve static page
    Route::put('pages/{slug}', [StaticPageController::class, 'update']); // Update static page


    Route::prefix('forum')->group(function () {
        Route::get('posts', [ForumPostController::class, 'index']);
        Route::post('posts', [ForumPostController::class, 'store']);
        Route::get('posts/{id}', [ForumPostController::class, 'show']);
        Route::put('posts/{id}', [ForumPostController::class, 'update']);
        Route::delete('posts/{id}', [ForumPostController::class, 'destroy']);
        Route::patch('posts/{id}/draft-toggle', [ForumPostController::class, 'toggleDraft']);
        Route::post('attachments/upload', [ForumAttachmentController::class, 'upload']);
        Route::delete('attachments/{id}', [ForumAttachmentController::class, 'destroy']);

        Route::post('posts/{id}/like', [ForumLikeController::class, 'toggleLike']);

        Route::post('posts/{id}/comments', [ForumCommentController::class, 'store']);
        Route::post('comments/{id}/react', [ForumReactionController::class, 'toggleReaction']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('{id}', [CategoryController::class, 'update']);
        Route::delete('{id}', [CategoryController::class, 'destroy']);
        Route::patch('{id}/status', [CategoryController::class, 'changeStatus']);
    });

    Route::prefix('subcategories')->group(function () {
        Route::get('/', [SubCategoryController::class, 'index']);
        Route::post('/', [SubCategoryController::class, 'store']);
        Route::put('{id}', [SubCategoryController::class, 'update']);
        Route::delete('{id}', [SubCategoryController::class, 'destroy']);
        Route::patch('{id}/status', [SubCategoryController::class, 'changeStatus']);
        Route::get('by-category/{categoryId}', [SubCategoryController::class, 'getByCategory']);
        Route::post('vehicles', [SubCategoryController::class, 'getVehiclesByCategoryAndSubcategory']);
    });

    Route::get('/inspection-requests', [InspectionRequestController::class, 'index']);
    Route::post('/inspection-requests', [InspectionRequestController::class, 'store']);
    Route::get('/inspection-requests/{id}', [InspectionRequestController::class, 'show']);
    Route::put('/inspection-requests/{id}', [InspectionRequestController::class, 'update']);
    Route::delete('/inspection-requests/{id}', [InspectionRequestController::class, 'destroy']);

    Route::get('/inspectors', [InspectionRequestController::class, 'getInspectors']);


        Route::get('/cards', [CardController::class, 'index']);
        Route::post('/cards', [CardController::class, 'store']);
        Route::get('cards/{id}', [CardController::class, 'show']);
        Route::put('cards/{id}', [CardController::class, 'update']);
        Route::delete('cards/{id}', [CardController::class, 'destroy']);

});


Route::prefix('dropdowns')->group(function () {
    Route::get('/makes', [VehicleDropdownController::class, 'makes']);
    Route::get('/models', [VehicleDropdownController::class, 'models']);
    Route::get('/years', [VehicleDropdownController::class, 'years']);
    Route::get('/mileages', [VehicleDropdownController::class, 'mileages']);
    Route::get('/fuel-types', [VehicleDropdownController::class, 'fuelTypes']);
    Route::get('/transmission-types', [VehicleDropdownController::class, 'transmissionTypes']);
    Route::get('/states', [VehicleDropdownController::class, 'states']);
    Route::get('/cities', [VehicleDropdownController::class, 'cities']); // use ?state_id=1 in query string
    Route::get('/registration-statuses', [VehicleDropdownController::class, 'registrationStatuses']);
    Route::get('/engine-modifications', [VehicleDropdownController::class, 'engineModifications']);
    Route::get('/exhaust-systems', [VehicleDropdownController::class, 'exhaustSystems']);
    Route::get('/suspensions', [VehicleDropdownController::class, 'suspensions']);
    Route::get('/wheels-tires', [VehicleDropdownController::class, 'wheelsTires']);
    Route::get('/brakes', [VehicleDropdownController::class, 'brakes']);
    Route::get('/body-kits', [VehicleDropdownController::class, 'bodyKits']);
    Route::get('/interior-upgrades', [VehicleDropdownController::class, 'interiorUpgrades']);
    Route::get('/performance-tunings', [VehicleDropdownController::class, 'performanceTunings']);
    Route::get('/electronics', [VehicleDropdownController::class, 'electronics']);
    Route::get('/interior-exteriors', [VehicleDropdownController::class, 'interiorExteriors']);
    Route::get('/all', [VehicleDropdownController::class, 'getAllDropdowns']);

});
