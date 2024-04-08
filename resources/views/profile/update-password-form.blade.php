<div>
    <div class="mb-3">
        <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
        <input type="password" id="current_password" class="form-control"
            wire:model="state.current_password"
            autocomplete="current-password" >
        <x-input-error for="current_password" class="mt-2" />
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">{{ __('New Password') }}</label>
        <input type="password" id="password" class="form-control"
            wire:model="state.password"
            autocomplete="password" autocomplete="new-password"/>
        <x-input-error for="password" class="mt-2" />
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
        <input type="password" id="password_confirmation" class="form-control"
            wire:model="state.password_confirmation" autocomplete="new-password"/>
        <x-input-error for="password_confirmation" class="mt-2" />
    </div>

    <div>
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <button class="btn btn-primary">
            {{ __('Save') }}
        </button>
    </div>

</div>
