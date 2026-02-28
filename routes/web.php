<?php

use App\Http\Controllers\CheckoutController;
use App\Models\ContactSetting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check() && auth()->user()->role === 'admin') {
        return redirect('/admin');
    }

	$contact = ContactSetting::query()->first();

	return view('welcome', [
		'contact' => [
			'location_line_1' => $contact?->location_line_1 ?? '123 Ember Street',
			'location_line_2' => $contact?->location_line_2 ?? 'Downtown, PH 1000',
			'hours_line_1' => $contact?->hours_line_1 ?? 'Mon - Sat: 10 AM - 8 PM',
			'hours_line_2' => $contact?->hours_line_2 ?? 'Sun: 12 PM - 6 PM',
			'phone' => $contact?->phone ?? '+63 900 000 0000',
			'email' => $contact?->email ?? 'hello@blackember.com',
		],
	]);
})->name('home');

// Checkout (Stripe)
Route::middleware('auth')->get('/checkout/create', [CheckoutController::class, 'create'])->name('checkout.create');
Route::get('/booking/success', [CheckoutController::class, 'success'])->name('booking.success');
Route::get('/booking/cancel',  [CheckoutController::class, 'cancel'])->name('booking.cancel');


Route::get('/book-appointment', function () {
	if (auth()->user()?->role === 'admin') {
		return redirect('/admin');
	}

	return redirect()->route('home', ['booking' => 1]);
})->middleware('auth')->name('book.appointment');

Route::middleware('auth')->group(function () {
	Route::get('/dashboard', function () {
		if (auth()->user()?->role === 'admin') {
			return redirect('/admin');
		}

		return redirect()->route('home');
	})->name('dashboard');

	Route::view('/profile', 'profile')->name('profile');

	Route::post('/logout', function () {
		auth()->guard('web')->logout();
		request()->session()->invalidate();
		request()->session()->regenerateToken();

		return redirect()->route('home');
	})->name('logout');
});

require __DIR__.'/auth.php';

