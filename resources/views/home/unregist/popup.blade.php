<div>
    @if($member_out)
    <div class="py-4">
        {{$message}}
    </div>
    @endif


    <div>
        @if($member_out)
        <button class="btn btn-primary" wire:click="cancelOut">탈퇴 취소</button>
        @else
        <button class="btn btn-danger" wire:click="popupComfirm">탈퇴 신청</button>
        @endif
    </div>



    @if ($popupConfirm)
        <x-table-dialog-modal wire:model="popupConfirm" maxWidth="xl">

            <x-slot name="title">
                {{ __('회원탈퇴') }}
            </x-slot>

            <x-slot name="content">
                <h2 class="mb-2">회원 가입을 탈퇴 합니다.</h2>
                <div class="fs-3 text-danger mb-2">
                    {{$randomCode}}
                </div>
                <div class="text-danger mb-4">
                    위의 코드를 다시 한번 입력해 주시길 바랍니다.
                </div>


                <div class="mb-3">
                    <input type="text" id="simpleinput" class="form-control"
                    wire:model="confirm_code">
                </div>

                @if($error)
                <div class="my-2">
                    {{$error}}
                </div>
                @endif

            </x-slot>

            <x-slot name="footer">
                <x-flex-between>
                    <div>

                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary"
                            wire:click="popupComfirmClose">취소</button>


                        <button type="button" class="btn btn-primary"
                            wire:click="submitConfirm">예 신청합니다.</button>

                    </div>
                </x-flex-between>
            </x-slot>
        </x-table-dialog-modal>
    @endif
</div>
