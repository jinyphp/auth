<x-admin>

    <h1 class="h3 mb-3">다중 연락처</h1>

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
                    href="/admin/auth/user/address/detail/{{$id}}">
                        다중 주소록
                    </a>
                    <a class="list-group-item list-group-item-action"
                    href="/admin/auth/user/phone/detail/{{$id}}">
                        다중 연락처
                    </a>
                </div>

            </div>
        </div>

        <div class="col-md-8 col-xl-9">


            @livewire('profile-phone',[
                        'viewFile' => "jiny-profile::home.user.profile.phone",
                    ])



        </div>
    </div>

</x-admin>
