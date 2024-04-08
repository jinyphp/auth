<div>

    <div class="mt-5">
        <button class="btn btn-danger"
        wire:click="confirmUserDeletion" wire:loading.attr="disabled">
            {{ __('Delete Account') }}
        </button>
    </div>

    <!-- Delete User Confirmation Modal -->
    <x-dialog-modal wire:model.live="confirmingUserDeletion">
        <x-slot name="title">
            {{ __('Delete Account') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}

            <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                <input type="password" class="mt-1 block w-3/4"
                            autocomplete="current-password"
                            placeholder="{{ __('Password') }}"
                            x-ref="password"
                            wire:model="password"
                            wire:keydown.enter="deleteUser" />

                <input-error for="password" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </button>

            <button class="ms-3" wire:click="deleteUser" wire:loading.attr="disabled">
                {{ __('Delete Account') }}
            </button>
        </x-slot>
    </x-dialog-modal>


</div>


