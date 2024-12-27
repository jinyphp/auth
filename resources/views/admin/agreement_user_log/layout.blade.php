<x-admin>

    <x-flex-between>
        <div class="page-title-box">
            <x-flex class="align-items-center gap-2">
                <h1 class="align-middle h3 d-inline">
                    @if (isset($actions['title']))
                        {{ $actions['title'] }}
                    @endif
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
        @if (isset($user))
            <div class="col-md-4 col-xl-3">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Profile Details</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="/home/user/avatar/{{ $user->id }}" alt="{{ $user->name }}"
                            class="img-fluid rounded-circle mb-2" width="128" height="128">
                        <h5 class="card-title mb-0">{{ $user->name }}</h5>

                    </div>

                </div>
            </div>
        @endif

        <div class="col-md-8 col-xl-9">
            {{-- 테이블 --}}
            @livewire('table-delete-create', [
                'actions' => $actions,
            ])
        </div>
    </div>

    {{-- 팝업 --}}
    @livewire('form-popup', [
        'actions' => $actions,
    ])


</x-admin>
