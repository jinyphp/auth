<div>
    <x-loading-indicator/>

    <div class="mb-3">
        <label for="inputPassword5" class="form-label">기존 비밀번호</label>
        <input type="password" id="inputPassword5" class="form-control"
            aria-describedby="passwordHelpBlock"
            wire:model.defer="form.old">

        @if(isset($message['old']))
        <div id="passwordHelpBlock" class="form-text text-danger">
            {{$message['old']}}
        </div>
        @else
            @if(!isset($form['old']))
        <div id="passwordHelpBlock" class="form-text">
            패스워드는 최소 8~20자 이내 이어야 합니다. 또한, @,#,! 특수기회와 대소문자가 같이 있어야 합니다.
        </div>
            @endif
        @endif
    </div>

    <div class="mb-3">
        <label for="inputPassword5" class="form-label">신규 비밀번호</label>
        <input type="password" id="inputPassword5" class="form-control"
            aria-describedby="passwordHelpBlock"
            wire:model.defer="form.new">
        @if(isset($message['new']))
        <div id="passwordHelpBlock" class="form-text">
            {{$message['new']}}
        </div>
        @else
            @if(!isset($form['new']))
        <div id="passwordHelpBlock" class="form-text">
            패스워드는 최소 8~20자 이내 이어야 합니다. 또한, @,#,! 특수기회와 대소문자가 같이 있어야 합니다.
        </div>
            @endif
        @endif

    </div>

    <div class="mb-3">
        <label for="inputPassword5" class="form-label">비밀번호 재확인</label>
        <input type="password" id="inputPassword5" class="form-control"
            aria-describedby="passwordHelpBlock"
            wire:model.defer="form.confirm">
        @if(isset($message['confirm']))
        <div id="passwordHelpBlock" class="form-text">
            {{$message['confirm']}}
        </div>
        @else
            @if(!isset($form['confirm']))
        <div id="passwordHelpBlock" class="form-text">
            패스워드는 최소 8~20자 이내 이어야 합니다. 또한, @,#,! 특수기회와 대소문자가 같이 있어야 합니다.
        </div>
            @endif
        @endif
    </div>



    <div class="col-12 mt-4">
        <x-flex-between>
            <div>
                @if(isset($success))
                <span class="text-success">
                    {{$success}}
                </span>
                @endif
            </div>
            <div>
                <button class="btn btn-light" type="cancel">취소</button>
                <button class="btn btn-primary me-2" wire:click="submit">변경</button>
            </div>
        </x-flex-between>
    </div>

    @if ($popupPassword)
        <x-table-dialog-modal wire:model="popupPassword" maxWidth="xl">

            <x-slot name="title">
                {{ __('비밀번호 변경') }}
            </x-slot>

            <x-slot name="content">
                @if(isset($error))
                <span class="text-danger">
                    {{$error}}
                </span>
                @else
                <p class="p-8">비밀번호를 변경합니다.</p>
                @endif
            </x-slot>

            <x-slot name="footer">
                <x-flex-between>
                    <div>

                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary"
                            wire:click="popupPasswordClose">취소</button>

                        @if(!$error)
                        <button type="button" class="btn btn-primary"
                            wire:click="submitConfirm">변경</button>
                        @endif
                    </div>
                </x-flex-between>
            </x-slot>
        </x-table-dialog-modal>
    @endif

</div>
