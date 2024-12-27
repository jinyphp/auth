<x-admin>
    {{-- <x-admin-layout>
    </x-admin-layout> --}}

    <h1 class="h3 mb-3">패스워드</h1>

    <div class="row">
        <div class="col-md-4 col-xl-3">
            <div class="card mb-3">

                <div class="card-body text-center">
                    <img src="/home/user/avatar/{{ $id }}" alt="{{ $user->name }}"
                        class="img-fluid rounded-circle mb-2" width="128" height="128">
                    <h5 class="card-title mb-0">{{ $user->name }}</h5>
                    <div class="text-muted mb-2">{{ $user->email }}</div>

                </div>
                <hr class="mb-0">
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action"
                    href="/admin/auth/user/password/detail/{{$id}}">
                        패스워드
                    </a>
                </div>

            </div>
        </div>

        <div class="col-md-8 col-xl-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">만료일자</h5>
                </div>
                <div class="card-body">
                    @livewire('profile-password-expire', [
                        'user_id' => $id,
                    ])
                </div>
            </div>


            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">패스워드</h5>
                </div>
                <div class="card-body">
                    {{-- 패스워드 변경 폼 --}}
                    {{-- @livewire('profile-password', [
                        'user_id' => $id,
                    ]) --}}

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
