<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MakeupArtistController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\CustomerMuaController;
use App\Http\Controllers\CustomerBookingController;
use App\Http\Controllers\MuaBookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerAiController;
use App\Http\Controllers\ChatController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {

    $user = auth()->user();

    if ($user->role === 'customer') {
        return view('customer.dashboard');
    }

    if ($user->role === 'makeup_artist') {

        // New MUA has not completed MUA registration yet
        if (!$user->makeupArtist) {
            return redirect()->route('makeup-artist.register');
        }

        return redirect()->route('mua.dashboard');
    }

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    abort(403);

})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/mua/register',
        [MakeupArtistController::class, 'create']
    )->name('makeup-artist.register');

    Route::post('/mua/register',
        [MakeupArtistController::class, 'store']
    )->name('makeup-artist.register.store');

    Route::get('/mua/review',
        [MakeupArtistController::class, 'review']
    )->name('mua.review');

    Route::post('/mua/publish',
        [MakeupArtistController::class, 'publish']
    )->name('makeup-artist.publish');

    Route::get('/mua/dashboard', function () {

        $user = auth()->user();
    
        if (!$user->makeupArtist) {
            return redirect()->route('makeup-artist.register');
        }
    
        return view('mua.dashboard');
    
    })->middleware('auth')->name('mua.dashboard');

    Route::get('/mua/profile',
        [MakeupArtistController::class, 'profile']
    )->name('mua.profile');

    Route::put('/mua/profile',
        [MakeupArtistController::class, 'updateProfile']
    )->name('mua.profile.update');

    Route::get('/mua/portfolio/create',
        [PortfolioController::class, 'create']
)   ->name('mua.portfolio.create');

    Route::post('/mua/portfolio',
        [PortfolioController::class, 'store']
    )->name('mua.portfolio.store');

    Route::get('/mua/portfolio/{portfolio}/edit',
        [PortfolioController::class, 'edit']
    )->name('mua.portfolio.edit');

    Route::put('/mua/portfolio/{portfolio}',
        [PortfolioController::class, 'update']
    )->name('mua.portfolio.update');

    Route::delete('/mua/portfolio/{portfolio}',
        [PortfolioController::class, 'destroy']
    )->name('mua.portfolio.destroy');

    Route::get('/mua/services', 
        [ServiceController::class, 'index'])
    ->name('mua.services');

    Route::get('/mua/services/create', 
        [ServiceController::class, 'create'])
    ->name('mua.services.create');

    Route::post('/mua/services', 
        [ServiceController::class, 'store'])
    ->name('mua.services.store');

    Route::get('/mua/services/{service}/edit', 
        [ServiceController::class, 'edit'])
    ->name('mua.services.edit');

    Route::put('/mua/services/{service}', 
        [ServiceController::class, 'update'])
    ->name('mua.services.update');

    Route::delete('/mua/services/{service}', 
        [ServiceController::class, 'destroy'])
    ->name('mua.services.destroy');

    Route::get('/mua/availability', 
        [AvailabilityController::class, 'index'])
    ->name('mua.availability');

    Route::put('/mua/availability', 
        [AvailabilityController::class, 'update'])
    ->name('mua.availability.update');

    Route::post('/mua/availability/block-date',
        [AvailabilityController::class, 'blockDate']
    )->name('mua.availability.block');

    Route::delete('/mua/availability/block-date/{blockedDate}',
        [AvailabilityController::class, 'removeBlockedDate']
    )->name('mua.availability.block.remove');

    Route::post('/mua/availability/custom-hours',
        [AvailabilityController::class, 'storeOverride']
    )->name('mua.availability.override.store');
    
    Route::delete('/mua/availability/custom-hours/{availabilityOverride}',
        [AvailabilityController::class, 'removeOverride']
    )->name('mua.availability.override.remove');

    Route::get('/customer/search-mua',
        [CustomerMuaController::class, 'index']
    )->middleware('auth')->name('customer.mua.index');

    Route::get('/customer/mua/{makeupArtist}',
        [CustomerMuaController::class, 'show']
    )->middleware('auth')->name('customer.mua.show');

    Route::post(
        '/customer/booking/payment',
        [CustomerBookingController::class, 'payment']
    )
    ->middleware('auth')->name('customer.booking.payment');

    Route::post(
        '/customer/booking/payment/process',
        [CustomerBookingController::class, 'processPayment']
    )
    ->middleware('auth')->name('customer.booking.payment.process');

    Route::get(
        '/customer/booking/{booking}/confirmation',
        [CustomerBookingController::class, 'confirmation']
    )
    ->middleware('auth')->name('customer.booking.confirmation');

    Route::get('/customer/booking/{service}',
        [CustomerBookingController::class, 'create']
    )->middleware('auth')->name('customer.booking.create');

    Route::get('/mua/bookings',
        [MuaBookingController::class, 'index']
    )
    ->middleware('auth') ->name('mua.bookings.index');
    
    Route::post('mua/bookings/{booking}/accept',
        [MuaBookingController::class, 'accept']
    )
    ->middleware('auth')->name('mua.bookings.accept');
    
    Route::post('/mua/bookings/{booking}/reject',
        [MuaBookingController::class, 'reject']
    )
    ->middleware('auth')->name('mua.bookings.reject');

    Route::get('/customer/bookings',
        [CustomerBookingController::class, 'index']
    )
    ->middleware('auth')->name('customer.bookings.index');

    Route::get('/customer/bookings/{booking}',
        [CustomerBookingController::class, 'show']
    )
    ->middleware('auth')->name('customer.bookings.show');

    Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'dashboard'])
            ->name('dashboard');
            
        Route::get('/users', [AdminController::class, 'users'])
            ->name('users.index');

        Route::get('/muas', [AdminController::class, 'muas'])
            ->name('muas.index');

        Route::get('/bookings', [AdminController::class, 'bookings'])
            ->name('booking.index');

        Route::get('/muas/{makeupArtist}', 
            [AdminController::class, 'showMua'])
        ->name('muas.show');
    
        Route::post('/muas/{makeupArtist}/toggle-publish', 
            [AdminController::class, 'toggleMuaPublish'])
        ->name('muas.togglePublish');

        Route::get('/bookings/{booking}', 
            [AdminController::class, 'showBooking'])
        ->name('bookings.show');

        Route::get('/users/{user}', 
            [AdminController::class, 'showUser'])
        ->name('users.show');
    
    });

    Route::get('/customer/payment/toyyibpay/return',
        [CustomerBookingController::class, 'toyyibpayReturn']
    )->name('customer.booking.toyyibpay.return');
    
    Route::post('/customer/payment/toyyibpay/callback',
        [CustomerBookingController::class, 'toyyibpayCallback']
    )->name('customer.booking.toyyibpay.callback');

    Route::middleware('auth')->group(function () {

        Route::get('/customer/ai-makeup', 
            [CustomerAiController::class, 'index'])
        ->name('customer.ai.index');
    
        Route::post('/customer/ai-makeup/upload',
            [CustomerAiController::class, 'upload'])
        ->name('customer.ai.upload');
    
        Route::get('/customer/ai-makeup/{analysis}', 
            [CustomerAiController::class, 'show'])
        ->name('customer.ai.show');

        Route::post('/customer/ai-makeup/{analysis}/recommend',
            [CustomerAiController::class, 'recommend'])->name('customer.ai.recommend');
    });

    Route::get('/demo', function () {
        return view('demo');
    });

    Route::middleware('auth')->group(function () {

        Route::get('/chat/{booking}', [ChatController::class, 'show'])
            ->name('chat.show');
    
        Route::post('/chat/{booking}', [ChatController::class, 'send'])
            ->name('chat.send');
    });


  
});

require __DIR__.'/auth.php';
