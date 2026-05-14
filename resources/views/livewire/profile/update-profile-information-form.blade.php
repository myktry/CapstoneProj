<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $name_stego_png_base64 = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->phone = Auth::user()->phone ?? '';
        $this->name_stego_png_base64 = Auth::user()->name_stego_png_base64 ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20'],
            'name_stego_png_base64' => ['required', 'string'],
        ]);

        // Plaintext name for UI; stego PNG remains the embedded duplicate for demo / decode routes.
        $user->fill([
            'name' => trim($validated['name']),
            'name_stego_png_base64' => $validated['name_stego_png_base64'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header style="margin-bottom: 1rem;">
        <h2 style="font-size: 1.125rem; line-height: 1.75rem; font-weight: 600; color: #f4f4f5;">
            {{ __('Profile Information') }}
        </h2>

        <p style="margin-top: 0.25rem; font-size: 0.875rem; line-height: 1.25rem; color: #a1a1aa;">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" style="display: grid; gap: 1rem;">
        <input type="hidden" wire:model.defer="name_stego_png_base64" />
        <div>
            <label for="name" style="display:block; margin-bottom:0.35rem; font-size:0.875rem; font-weight:600; color:#d4d4d8;">{{ __('Name') }}</label>
            <input
                wire:model="name"
                id="name"
                name="name"
                type="text"
                style="width:100%; border:1px solid rgba(255,255,255,0.12); border-radius:0.75rem; background:#09090b; color:#fff; padding:0.8rem 0.95rem; outline:none;"
                required
                autofocus
                autocomplete="name"
            />
            <div style="margin-top:0.4rem; color:#fca5a5; font-size:0.875rem;">@foreach ($errors->get('name') as $message) <div>{{ $message }}</div> @endforeach</div>
        </div>

        <div>
            <label for="email" style="display:block; margin-bottom:0.35rem; font-size:0.875rem; font-weight:600; color:#d4d4d8;">{{ __('Email') }}</label>
            <input wire:model="email" id="email" name="email" type="email" required autocomplete="username" style="width:100%; border:1px solid rgba(255,255,255,0.12); border-radius:0.75rem; background:#09090b; color:#fff; padding:0.8rem 0.95rem; outline:none;" />
            <div style="margin-top:0.4rem; color:#fca5a5; font-size:0.875rem;">@foreach ($errors->get('email') as $message) <div>{{ $message }}</div> @endforeach</div>

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p style="margin-top:0.5rem; font-size:0.875rem; color:#d4d4d8;">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" type="button" style="margin-left:0.25rem; background:none; border:0; color:#fcd34d; text-decoration:underline; cursor:pointer; padding:0;">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p style="margin-top:0.5rem; font-size:0.875rem; font-weight:600; color:#4ade80;">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <label for="phone" style="display:block; margin-bottom:0.35rem; font-size:0.875rem; font-weight:600; color:#d4d4d8;">{{ __('Phone Number') }}</label>
            <input wire:model="phone" id="phone" name="phone" type="text" required autocomplete="tel" style="width:100%; border:1px solid rgba(255,255,255,0.12); border-radius:0.75rem; background:#09090b; color:#fff; padding:0.8rem 0.95rem; outline:none;" />
            <div style="margin-top:0.4rem; color:#fca5a5; font-size:0.875rem;">@foreach ($errors->get('phone') as $message) <div>{{ $message }}</div> @endforeach</div>
        </div>

        <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            <button
                type="submit"
                x-data="{ busy: false }"
                x-on:click.prevent="
                    if (busy) return;
                    busy = true;
                    try {
                        const name = (document.getElementById('name')?.value || '').trim();
                        if (!name) { busy = false; return; }
                        const cover = window.StegoDemo.createCoverImageLike({ width: 300, height: 300 });
                        const encoded = await window.StegoDemo.hideUserDataInImageLike(cover, { name });
                        const pngBase64 = window.StegoDemo.imageLikeToPngBase64(encoded);
                        await $wire.set('name_stego_png_base64', pngBase64);
                        $wire.updateProfileInformation();
                    } finally {
                        busy = false;
                    }
                "
                style="border:none; border-radius:9999px; background:#fbbf24; color:#111827; padding:0.8rem 1.25rem; font-weight:700; cursor:pointer;"
            >
                {{ __('Save') }}
            </button>

            <span style="color:#a1a1aa; font-size:0.875rem;" class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </span>
        </div>
    </form>
</section>
