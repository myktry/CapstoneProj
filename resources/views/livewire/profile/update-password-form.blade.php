<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header style="margin-bottom:1rem;">
        <h2 style="font-size:1.125rem; line-height:1.75rem; font-weight:600; color:#f4f4f5;">
            {{ __('Update Password') }}
        </h2>

        <p style="margin-top:0.25rem; font-size:0.875rem; line-height:1.25rem; color:#a1a1aa;">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form wire:submit="updatePassword" style="display:grid; gap:1rem;">
        <div>
            <label for="update_password_current_password" style="display:block; margin-bottom:0.35rem; font-size:0.875rem; font-weight:600; color:#d4d4d8;">{{ __('Current Password') }}</label>
            <input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" style="width:100%; border:1px solid rgba(255,255,255,0.12); border-radius:0.75rem; background:#09090b; color:#fff; padding:0.8rem 0.95rem; outline:none;" />
            <div style="margin-top:0.4rem; color:#fca5a5; font-size:0.875rem;">@foreach ($errors->get('current_password') as $message) <div>{{ $message }}</div> @endforeach</div>
        </div>

        <div>
            <label for="update_password_password" style="display:block; margin-bottom:0.35rem; font-size:0.875rem; font-weight:600; color:#d4d4d8;">{{ __('New Password') }}</label>
            <input wire:model="password" id="update_password_password" name="password" type="password" autocomplete="new-password" style="width:100%; border:1px solid rgba(255,255,255,0.12); border-radius:0.75rem; background:#09090b; color:#fff; padding:0.8rem 0.95rem; outline:none;" />
            <div style="margin-top:0.4rem; color:#fca5a5; font-size:0.875rem;">@foreach ($errors->get('password') as $message) <div>{{ $message }}</div> @endforeach</div>
        </div>

        <div>
            <label for="update_password_password_confirmation" style="display:block; margin-bottom:0.35rem; font-size:0.875rem; font-weight:600; color:#d4d4d8;">{{ __('Confirm Password') }}</label>
            <input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" style="width:100%; border:1px solid rgba(255,255,255,0.12); border-radius:0.75rem; background:#09090b; color:#fff; padding:0.8rem 0.95rem; outline:none;" />
            <div style="margin-top:0.4rem; color:#fca5a5; font-size:0.875rem;">@foreach ($errors->get('password_confirmation') as $message) <div>{{ $message }}</div> @endforeach</div>
        </div>

        <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            <button type="submit" style="border:none; border-radius:9999px; background:#fbbf24; color:#111827; padding:0.8rem 1.25rem; font-weight:700; cursor:pointer;">{{ __('Save') }}</button>

            <span style="color:#a1a1aa; font-size:0.875rem;" class="me-3" on="password-updated">
                {{ __('Saved.') }}
            </span>
        </div>
    </form>
</section>
