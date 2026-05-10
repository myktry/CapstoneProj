<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section style="display:grid; gap:1rem;">
    <header>
        <h2 style="font-size:1.125rem; line-height:1.75rem; font-weight:600; color:#f4f4f5;">
            {{ __('Delete Account') }}
        </h2>

        <p style="margin-top:0.25rem; font-size:0.875rem; line-height:1.25rem; color:#a1a1aa;">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        style="border:none; border-radius:9999px; background:rgba(239,68,68,0.95); color:#fff; padding:0.8rem 1.25rem; font-weight:700; cursor:pointer;"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" style="padding:1.5rem; background:#18181b; color:#f4f4f5;">

            <h2 style="font-size:1.125rem; line-height:1.75rem; font-weight:600; color:#fff;">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p style="margin-top:0.25rem; font-size:0.875rem; line-height:1.25rem; color:#a1a1aa;">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div style="margin-top:1rem;">
                <label for="password" style="position:absolute; left:-9999px;">{{ __('Password') }}</label>

                <input
                    wire:model="password"
                    id="password"
                    name="password"
                    type="password"
                    style="width:75%; border:1px solid rgba(255,255,255,0.12); border-radius:0.75rem; background:#09090b; color:#fff; padding:0.8rem 0.95rem; outline:none;"
                    placeholder="{{ __('Password') }}"
                />

                <div style="margin-top:0.4rem; color:#fca5a5; font-size:0.875rem;">@foreach ($errors->get('password') as $message) <div>{{ $message }}</div> @endforeach</div>
            </div>

            <div style="margin-top:1rem; display:flex; justify-content:flex-end; gap:0.75rem; flex-wrap:wrap;">
                <button type="button" x-on:click="$dispatch('close')" style="border:1px solid rgba(255,255,255,0.12); border-radius:9999px; background:#27272a; color:#e4e4e7; padding:0.8rem 1.2rem; cursor:pointer;">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" style="border:none; border-radius:9999px; background:#ef4444; color:#fff; padding:0.8rem 1.2rem; font-weight:700; cursor:pointer;">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
