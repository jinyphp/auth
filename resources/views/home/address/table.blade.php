<div>
    <x-loading-indicator/>

    @if(!$popupForm)
        {{-- 목록 표시 --}}
        @includeIf($viewList)


        <x-flex-center>
            <x-click wire:click="create">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                </svg>
            </x-click>
        </x-flex-center>
    @else
        {{-- inline 추가폼 --}}
        @includeIf($viewForm)

        <x-flex-between class="mt-3">
            <div>
                @if($edit_id)
                <button class="btn btn-danger me-2" wire:click="delete">삭제</button>
                @endif
            </div>
            <div>
                <button class="btn btn-light" wire:click="close">취소</button>
                @if($edit_id)
                <button class="btn btn-info me-2" wire:click="update">수정</button>
                @else
                <button class="btn btn-primary me-2" wire:click="store">등록</button>
                @endif
            </div>
        </x-flex-between>
    @endif
</div>

