<x-admin>

    <x-flex-between>
        <div class="page-title-box">
            <x-flex class="align-items-center gap-2">
                <h1 class="align-middle h3 d-inline">
                    {{-- @if (isset($actions['title']))
                        {{ $actions['title'] }}
                    @endif --}}
                    {{ $minor_name }}
                </h1>

            </x-flex>
            <p>
                @if (isset($actions['subtitle']))
                    {{ $actions['subtitle'] }}
                @endif
            </p>
        </div>

        <div class="page-title-box">
            <x-breadcrumb-item>
                {{ $actions['route']['uri'] }}
            </x-breadcrumb-item>

            <div class="mt-2 d-flex justify-content-end gap-2">
                <button class="btn btn-sm btn-danger">Video</button>
                <button class="btn btn-sm btn-secondary">Manual</button>
            </div>
        </div>
    </x-flex-between>

    <div class="row">
        <div class="col-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $minor_name }}</h5>
                    <p class="card-text">미성년자 보호자를 설정합니다.</p>
                </div>
            </div>
        </div>
        <div class="col-9">
            {{-- 테이블 --}}
            @livewire('table-delete-create', [
                'actions' => $actions,
            ])

            {{-- 팝업 --}}
            @livewire('form-popup', [
                'actions' => $actions,
            ])
        </div>
    </div>
</x-admin>
