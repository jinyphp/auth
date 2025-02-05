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
            <p>
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
        <div class="col-md-4 col-xl-3">
            @include('jiny-auth::admin.user_detail.side')
        </div>

        <div class="col-md-8 col-xl-9">
            {{-- 아바타 이미지 --}}
            <article class="card">
                <div class="card-header ">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title h5 mb-0">프로파일 사진변경</h5>
                        <a href="/admin/auth/avata" class="btn btn-sm btn-light">
                            View
                        </a>
                    </div>
                    <h6 class="card-subtitle text-muted">
                        프로필을 돋보이게 하고 사람들이 볼 수 있도록 사진을 업로드하세요.
                                귀하의 의견과 기여를 쉽게 인식하십시오!
                    </h6>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            @livewire('avata-image', [
                                'width' => '128px',
                                'user_id' => $id,
                            ])

                        </div>
                        <div class="col-9">
                            @livewire('avata-update', ['user_id' => $id])
                        </div>
                    </div>
                </div>
            </article>


            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">기본정보</h5>
                </div>
                <div class="card-body">
                    @livewire('admin-user_detail', [
                        'user_id' => $id,
                    ])
                </div>
            </div>


            {{-- 회원삭제 --}}
            <div class="card">
                <div class="card-header ">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title h5 mb-0">회원 삭제</h5>
                        <a href="/admin/auth/unregist" class="btn btn-sm btn-light">
                            View
                        </a>
                    </div>
                    <h6 class="card-subtitle text-muted">
                        등록된 회원의 모든 정보를 일괄 삭제 합니다.
                    </h6>
                </div>
                <div class="card-body">
                    @livewire('admin-user_delete', ['user_id' => $id])
                </div>
            </div>






        </div>
    </div>
</x-admin>
