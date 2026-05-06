<div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
    @if ($this->canBootstrap)
        <p class="text-sm font-semibold text-gray-700">Bootstrap Admin</p>
        <p class="mt-1 text-sm text-gray-600">No admin account exists yet. Create the first admin user here, then hide this form automatically.</p>

        @if (session()->has('admin-bootstrap-success'))
            <div class="mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('admin-bootstrap-success') }}
            </div>
        @endif

        <form wire:submit.prevent="createAdmin" class="mt-4 grid gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                <input type="text" wire:model="name" class="w-full rounded-md border border-gray-300 px-3 py-2" placeholder="Admin Name" />
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                <input type="email" wire:model="email" class="w-full rounded-md border border-gray-300 px-3 py-2" placeholder="admin@blackemberbarber.me" />
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Password</label>
                <input type="password" wire:model="password" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" wire:model="password_confirmation" class="w-full rounded-md border border-gray-300 px-3 py-2" />
            </div>

            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                Create Admin
            </button>
        </form>
    @else
        <p class="text-sm font-semibold text-gray-700">Admin bootstrap disabled</p>
        <p class="mt-1 text-sm text-gray-600">An admin account already exists. Use the admin panel or the <span class="font-mono">php artisan admin:user</span> command if you need to update one.</p>
    @endif
</div>