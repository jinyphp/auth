<x-admin>

    <x-flex-between>
        <div class="page-title-box">
            <x-flex class="align-items-center gap-2">
                <h1 class="align-middle h3 d-inline">
                    <a href="/admin/auth/user/{{ $id }}" class="text-decoration-none">
                        {{ $user->name }}
                    </a>
                </h1>
                <span class="badge bg-secondary">{{ $user->id }}</span>
            </x-flex>
            <p class="text-muted">
                {{ $actions['subtitle'] }}
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
        {{-- 서브메뉴 --}}
        <div class="col-md-4 col-xl-3">
            @include('jiny-auth::admin.user_detail.side')
        </div>

        
        <div class="col-md-8 col-xl-9">

            {{-- 회원승인 --}}
            @livewire('admin-user_detail.auth', ['user_id' => $id])

            {{-- 휴면관리 --}}
            @livewire('admin-user_detail.sleep', ['user_id' => $id])

            {{-- 이메일 검증 --}}
            @livewire('admin-user_detail.verify', ['user_id' => $id])

            {{-- 2FA --}}
            <div class="card flex-fill">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">2FA</label>

                    </div>
                </div>
            </div>



        </div>
    </div>




</x-admin>
