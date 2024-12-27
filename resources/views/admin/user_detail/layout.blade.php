<x-admin>
    <x-flex-between>
        <div class="page-title-box">
            <x-flex class="align-items-center gap-2">
                <h1 class="align-middle h3 d-inline">
                    가입일자 : {{$user->created_at}}
                </h1>
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
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">User Details</h5>
                </div>
                {{-- <div class="card-body text-center">
                    <img src="/home/user/avatar/{{$id}}" alt="{{$user->name}}"
                        class="img-fluid rounded-circle mb-2" width="128" height="128">
                    <h5 class="card-title mb-0">{{$user->name}}</h5>
                    <div class="text-muted mb-2">{{$user->email}}</div>

                    <div>
                        <a class="btn btn-primary btn-sm" href="#">Follow</a>
                        <a class="btn btn-primary btn-sm" href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="message-square" class="lucide lucide-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg> Message</a>
                    </div>
                </div> --}}
                <hr class="my-0">
                <div class="card-body">
                    <h5 class="h6 card-title">Balance</h5>
                    <h4 class="mt-0 mb-1">
                        @php
                            $row = DB::table('user_emoney')
                                ->where('user_id',$id)
                                ->first();
                            if($row) $balance = $row->balance;
                            else $balance = 0;
                        @endphp
                        <a href="/admin/auth/emoney/log/{{$id}}">
                            {{ $balance }}
                        </a>
                    </h4>
                </div>

            </div>

            <div class="card flex-fill">
                <div class="card-header">
                    <div class="card-actions float-end">
                        <a href="/admin/auth/logs/{{$id}}" class="text-decoration-none">
                            <button class="btn btn-sm btn-light">View all</button>
                        </a>
                    </div>
                    <h5 class="card-title mb-0">접속기록</h5>
                </div>
                <table class="table table-sm table-striped my-0">
                    <thead>
                        <tr>
                            <th>접속일자</th>
                            <th class="d-none d-xl-table-cell">방식</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach(DB::table('user_logs')
                        ->where('user_id',$id)
                        ->orderBy('created_at','desc')
                        ->limit(10)
                        ->get() as $log)
                        <tr>
                            <td>{{$log->created_at}}</td>
                            <td class="d-none d-xl-table-cell">{{$log->provider}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- center --}}
        <div class="col-md-4 col-xl-6">
            {{-- 아바타 이미지 --}}
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-4">
                        프로파일 사진변경
                    </h4>

                    <div class="row">
                        <div class="col-3">
                            @livewire('avata-image',[
                                'width'=>"128px",
                                'user_id'=>$id])

                        </div>
                        <div class="col-9">
                            <p class="text-muted font-14">
                                프로필을 돋보이게 하고 사람들이 볼 수 있도록 사진을 업로드하세요.
                                    귀하의 의견과 기여를 쉽게 인식하십시오!
                            </p>

                            @livewire('avata-update',['user_id'=>$id])
                        </div>
                    </div>

                </div>
            </div>

            <div class="d-flex gap-2 mb-4">
                <a class="btn btn-secondary"
                    href="/admin/auth/user/password/detail/{{$id}}">
                    패스워드 설정
                </a>

                <a class="btn btn-secondary"
                    href="/admin/auth/user/address/detail/{{$id}}">
                    다중 주소록
                </a>

                <a class="btn btn-secondary"
                    href="/admin/auth/user/phone/detail/{{$id}}">
                    다중 연락처
                </a>
            </div>


            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">기본정보</h5>
                </div>
                <div class="card-body">
                    @livewire('admin-user_detail',[
                        'user_id' => $id
                    ])
                </div>
            </div>


            @livewire('admin-user_delete',[
                'user_id' => $id
            ])














        </div>

        <div class="col-md-4 col-xl-3">
            {{-- 회원승인 --}}
            @livewire('admin-user_detail.auth',['user_id' => $id])

            {{-- 휴면관리 --}}
            @livewire('admin-user_detail.sleep',['user_id' => $id])

            <div class="card flex-fill">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">
                            <a href="/admin/auth/user/role/{{$id}}"
                                class="btn btn-sm btn-secondary">
                                역할 관리
                            </a>
                        </label>

                    </div>
                </div>
            </div>

            @livewire('admin-user_detail.verify',['user_id' => $id])

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
