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
        <div class="col-12 col-md-3">
            <div class="card">
                <div class="card-header">
                    <div class="mb-0 card-title h5">Settings</div>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/admin/auth/setting/login"
                        class="list-group-item list-group-item-action">
                        로그인
                    </a>

                    <a href="/admin/auth/setting"
                        class="list-group-item list-group-item-action">
                        인증
                    </a>
                    <a href="/admin/auth/setting/password"
                        class="list-group-item list-group-item-action">
                        페스워드
                    </a>

                    <a href="/admin/auth/setting/regist"
                        class="list-group-item list-group-item-action">
                        회원가입
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-9">
            @livewire('WireConfigPHP', ['actions' => $actions])
        </div>
    </div>


</x-admin>
