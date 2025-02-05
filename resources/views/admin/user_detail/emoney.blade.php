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
            <p class="text-muted">
                {{$actions['subtitle']}}
            </p>
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
            <div class="card flex-fill">
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




        </div>
    </div>




</x-admin>
