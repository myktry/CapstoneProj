<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\BookingNotificationController;
use App\Http\Controllers\BookingRefundController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Admin\ReceiptDecryptionController;
use App\Models\ContactSetting;
use App\Models\GalleryItem;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Livewire\Volt\Volt;
use App\Models\Appointment;

Route::get('/', function () {
	if (auth()->check() && auth()->user()?->isAdmin()) {
        return redirect('/admin');
    }

	$contact = null;

	if (Schema::hasTable('contact_settings')) {
		$contact = ContactSetting::query()->first();
	}

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

Route::get('/gallery', function () {
	if (auth()->check() && auth()->user()?->isAdmin()) {
		return redirect('/admin');
	}

	return view('gallery');
})->name('gallery');

Route::get('/featured-styles', function () {
	$items = Cache::remember('home.featured_styles', now()->addSeconds(60), function () {
		return GalleryItem::query()
			->select(['id', 'name', 'image', 'description'])
			->featuredOnHome()
			->limit(6)
			->get()
			->map(fn ($item) => [
				'name' => $item->name,
				'image' => $item->image
					? (str_starts_with($item->image, 'http') ? $item->image : Storage::disk('public')->url($item->image))
					: 'https://images.unsplash.com/photo-1503951458645-643d53bfd90f?q=80&w=1200&auto=format&fit=crop',
				'description' => $item->description ?: 'Premium grooming style showcase.',
			])->values();
	});

	return response()->json([
		'items' => $items,
	]);
})->name('home.featured-styles');

// Checkout (Stripe)
Route::middleware('auth')->get('/checkout/create', [CheckoutController::class, 'create'])->name('checkout.create');
Route::middleware('auth')->group(function () {
	Volt::route('/booking/verify-sms', 'pages.booking.verify-sms')
		->name('booking.verify-sms');
});
Route::get('/booking/success', [CheckoutController::class, 'success'])->name('booking.success');
Route::get('/booking/cancel',  [CheckoutController::class, 'cancel'])->name('booking.cancel');
Route::get('/webhooks/stripe', function () {
	return response('Stripe webhook endpoint is active. Use POST requests from Stripe/Stripe CLI.', 200);
});
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])
	->withoutMiddleware([VerifyCsrfToken::class])
	->name('webhooks.stripe');


Route::get('/book-appointment', function () {
	if (auth()->user()?->isAdmin()) {
		return redirect('/admin');
	}

	return redirect()->route('home', [
		'booking' => 1,
		'service' => request()->query('service'),
	]);
})->middleware('auth')->name('book.appointment');

Route::middleware('auth')->group(function () {
	Route::post('/notifications/bookings/mark-seen', [BookingNotificationController::class, 'markSeen'])
		->name('notifications.bookings.mark-seen');
	Route::get('/notifications', [BookingNotificationController::class, 'getNotifications'])
		->name('notifications.index');
	Route::patch('/notifications/{notification}/read', [BookingNotificationController::class, 'markAsRead'])
		->name('notifications.mark-read');
	Route::post('/notifications/mark-all-read', [BookingNotificationController::class, 'markAllAsRead'])
		->name('notifications.mark-all-read');

	Route::get('/my-bookings/{appointment}', [BookingRefundController::class, 'show'])
		->name('bookings.show');
	Route::post('/my-bookings/{appointment}/cancel', [BookingRefundController::class, 'cancel'])
		->name('bookings.cancel');

	Route::get('/dashboard', function () {
		if (auth()->user()?->isAdmin()) {
			return redirect('/admin');
		}

		return redirect()->route('home');
	})->name('dashboard');

	Route::get('/profile', function () {
		$user = request()->user();

		return view('profile', [
			'notifications' => $user?->notifications()->latest()->limit(10)->get() ?? collect(),
			'unreadNotificationCount' => $user?->unreadNotifications()->count() ?? 0,
		]);
	})->name('profile');

	Route::post('/logout', function () {
		auth()->guard('web')->logout();
		request()->session()->invalidate();
		request()->session()->regenerateToken();

		return redirect()->route('home');
	})->name('logout');

	Route::post('/admin/security/receipts/decrypt', [ReceiptDecryptionController::class, 'decrypt'])
		->middleware('throttle:receipt-decrypt')
		->name('admin.security.receipts.decrypt');

	Route::get('/stego/test/{appointment}', function (Appointment $appointment) {
		abort_unless((int) $appointment->user_id === (int) request()->user()?->id, 403);

		return view('stego.test', [
			'appointment' => $appointment->loadMissing('service'),
		]);
	})->name('stego.test');

	Route::get('/stego/test-latest', function () {
		$appointment = Appointment::query()
			->where('user_id', auth()->id())
			->latest('id')
			->firstOrFail();

		return redirect()->route('stego.test', ['appointment' => $appointment->id]);
	})->name('stego.test-latest');

	Route::get('/stego/user', function () {
		$user = request()->user();

		return view('stego.user', [
			'user' => $user,
		]);
	})->name('stego.user');

	Route::get('/admin/bootstrap', function () {
		abort_unless(\App\Models\User::query()->where('role', 'admin')->count() === 0, 404);

		return view('admin.bootstrap');
	})->name('admin.bootstrap');
});

require __DIR__.'/auth.php';

