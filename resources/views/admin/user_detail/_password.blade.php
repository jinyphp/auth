<div class="card">
    <div class="card-header d-flex justify-content-between">
        <a href="/admin/auth/user/password/detail/{{$id}}"
            class="text-decoration-none">
            <h5 class="card-title mb-0">패스워드</h5>
        </a>

        {{-- 패스워드 리셋 버튼 --}}
        @livewire('profile-password-reset',[
            'user_id' => $id,
        ])
    </div>
    <div class="card-body">
        {{-- 패스워드 변경 폼 --}}
        @livewire('admin-user-password',[
            'user_id' => $id,
        ])

    </div>
</div>
