<x-admin>

    <x-flex-between>
        <div class="page-title-box">
            <x-flex class="align-items-center gap-2">
                <h1 class="align-middle h3 d-inline">
                    <a href="/admin/auth/user/{{$id}}" class="text-decoration-none">
                        {{$user->name}}
                    </a>
                </h1>
                <span class="badge bg-secondary">{{$user->id}}</span>
            </x-flex>
        </div>

        <div class="page-title-box">
            <x-breadcrumb-item>
                {{$actions['route']['uri']}}
            </x-breadcrumb-item>

            <div class="mt-2 d-flex justify-content-end gap-2">
                <button class="btn btn-sm btn-danger">Video</button>
                <button class="btn btn-sm btn-secondary">Manual</button>
            </div>
        </div>
    </x-flex-between>

    <div class="row">
        <div class="col-md-4 col-xl-3">
            @include('jiny-auth::admin.user_detail.side')
        </div>

        <div class="col-md-8 col-xl-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">만료일자</h5>

                    <a href="/admin/auth/password" class="btn btn-light btn-sm">
                        패스워드 목록
                    </a>
                </div>
                <div class="card-body">
                    @livewire('profile-password-expire', [
                        'user_id' => $id,
                    ])
                </div>
            </div>


            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">패스워드 변경</h5>

                    <a href="/admin/auth/setting/password" class="btn btn-light btn-sm">
                        설정
                    </a>
                </div>
                <div class="card-body">
                    {{-- 패스워드 변경 폼 --}}
                    @livewire('admin-user-password',[
                        'user_id' => $id,
                    ])
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="h6 card-title">리셋메일</h5>
                    <div class="text-muted mb-2">페스워드 초기화 메일을 발송합니다.</div>

                    <div class="d-flex justify-content-end">
                        {{-- 패스워드 리셋 버튼 --}}
                        @livewire('profile-password-reset', [
                            'user_id' => $id,
                        ])
                    </div>

                </div>
            </div>
        </div>
    </div>

</x-admin>
