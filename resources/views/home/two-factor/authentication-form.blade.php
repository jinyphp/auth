<div>


    <div class="d-flex justify-content-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900">
                @if ($this->enabled)
                    @if ($showingConfirmation)
                        {{ __('2단계 인증 활성화를 완료하세요.') }}
                    @else
                        {{ __('2단계 인증이 활성화되었습니다.') }}
                    @endif
                @else
                    {{ __('2단계 인증이 활성화되지 않았습니다.') }}
                @endif
            </h3>

            <div class="mt-3 max-w-xl text-sm text-gray-600">
                <p>
                    {{ __('2단계 인증이 활성화되면 인증 시 보안을 위한 랜덤 토큰을 입력해야 합니다. 휴대폰의 Google Authenticator 애플리케이션에서 이 토큰을 가져올 수 있습니다.') }}
                </p>
            </div>

            @if ($this->enabled)
                @if ($showingQrCode)
                    <div class="mt-4 max-w-xl text-sm text-gray-600">
                        <p class="font-semibold">
                            @if ($showingConfirmation)
                                {{ __('2단계 인증을 완료하려면 다음 QR 코드를 휴대폰의 인증 애플리케이션에서 스캔하거나 설정 키와 생성된 OTP 코드를 입력하세요.') }}
                            @else
                                {{ __('2단계 인증이 활성화되었습니다. 다음 QR 코드를 휴대폰의 인증 애플리케이션에서 스캔하거나 설정 키를 입력하세요.') }}
                            @endif
                        </p>
                    </div>

                    <div class="mt-4 p-2 inline-block bg-white">
                        {!! $this->user->twoFactorQrCodeSvg() !!}
                    </div>

                    <div class="mt-4 max-w-xl text-sm text-gray-600">
                        <p class="font-semibold">
                            {{ __('Setup Key') }}: {{ decrypt($this->user->two_factor_secret) }}
                        </p>
                    </div>

                    @if ($showingConfirmation)
                        <div class="mt-4">
                            <label for="code">{{ __('Code') }}</label>

                            <input id="code" type="text" name="code" class="block mt-1 w-1/2"
                                inputmode="numeric" autofocus autocomplete="one-time-code" wire:model="code"
                                wire:keydown.enter="confirmTwoFactorAuthentication" />

                            <input-error for="code" class="mt-2" />
                        </div>
                    @endif
                @endif

                @if ($showingRecoveryCodes)
                    <div class="mt-4 max-w-xl text-sm text-gray-600">
                        <p class="font-semibold">
                            {{ __('이 복구 코드를 안전한 비밀번호 관리자에 저장하세요. 2단계 인증 장치를 잃어버렸을 때 계정에 접근하는 데 사용할 수 있습니다.') }}
                        </p>
                    </div>

                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 rounded-lg">
                        @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                            <div>{{ $code }}</div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
        <div>
            @if (!$this->enabled)
                <x-confirms-password wire:then="enableTwoFactorAuthentication">
                    <button class="btn btn-primary" type="button" wire:loading.attr="disabled">
                        {{ __('Enable') }}
                    </button>
                </x-confirms-password>
            @else
                @if ($showingRecoveryCodes)
                    <x-confirms-password wire:then="regenerateRecoveryCodes">
                        <button class="btn btn-secondary" class="me-3">
                            {{ __('Regenerate Recovery Codes') }}
                        </button>
                    </x-confirms-password>
                @elseif ($showingConfirmation)
                    <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                        <button class="btn btn-primary" type="button" class="me-3" wire:loading.attr="disabled">
                            {{ __('Confirm') }}
                        </button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="showRecoveryCodes">
                        <button class="btn btn-secondary" class="me-3">
                            {{ __('Show Recovery Codes') }}
                        </button>
                    </x-confirms-password>
                @endif

                @if ($showingConfirmation)
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <button class="btn btn-secondary" wire:loading.attr="disabled">
                            {{ __('Cancel') }}
                        </button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <button class="btn btn-danger" wire:loading.attr="disabled">
                            {{ __('Disable') }}
                        </button>
                    </x-confirms-password>
                @endif

            @endif
        </div>
    </div>
</div>
