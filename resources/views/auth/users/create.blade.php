<x-theme theme="admin.sidebar2">
    <x-theme-layout>
        <x-container-fluid>

            <!-- start page title -->
            <x-row>
                <x-col class="col-8">
                    <div class="page-title-box">
                        <ol class="m-0 breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Users</a></li>
                            <li class="breadcrumb-item active">List</li>
                        </ol>

                        <div class="mb-3">
                            <h1 class="align-middle h3 d-inline"><strong>회원</strong> 목록</h1>
                            <p></p>
                        </div>
                    </div>
                </x-col>
            </x-row>
            <!-- end page title -->

            <x-row>
                <div class="col-lg-2">
                    @include("jinyadmin::users.submenu")
                </div>
                <div class="col-lg-10">
                    <x-card>
                        <x-card-header>
                            회원 추가
                        </x-card-header>
                        <x-card-body>

                            <x-row>
                                <div class="col-sm-10 col-md-8 col-lg-6">

                                    <x-form-post action="{{route('admin.users.list.store')}}">

                                        <x-form-hor>
                                            <x-form-label>
                                                {{ __('Name') }}
                                            </x-form-label>
                                            <x-form-item>
                                                <input class="form-control form-control-lg" type="text" name="name"
                                                    placeholder="회원 이름을 입력해 주세요." :value="old('name')" @error('name') is-invalid
                                                    @enderror autofocus>
                                                @error('name') {{$message}} @enderror
                                            </x-form-item>
                                            </x-form-row>

                                            <x-form-hor>
                                                <x-form-label>
                                                    {{ __('Email') }}
                                                </x-form-label>
                                                <x-form-item>
                                                    <input class="form-control form-control-lg" type="text" name="email"
                                                        placeholder="이메일 주소를 입력해 주세요." :value="old('email')" @error('email')
                                                        is-invalid @enderror autofocus>
                                                    @error('email') {{$message}} @enderror
                                                </x-form-item>
                                                </x-form-row>

                                                <x-form-hor>
                                                    <x-form-label>
                                                        {{ __('Password') }}
                                                    </x-form-label>
                                                    <x-form-item>
                                                        <input class="form-control form-control-lg" type="password"
                                                            name="password" placeholder="" @error('password') is-invalid
                                                            @enderror autofocus>
                                                        @error('password') {{$message}} @enderror
                                                    </x-form-item>
                                                    </x-form-row>

                                                    {{-- 권환 롤설정 --}}
                                                    <div>
                                                        @foreach ($roles as $role)
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" name="roles[]"
                                                                value="{{$role->id}}" id="{{$role->name}}">
                                                            <label class="form-check-label"
                                                                for="{{$role->name}}">{{$role->name}}</label>

                                                        </div>
                                                        @endforeach
                                                    </div>

                                                    <x-form-submit class="btn btn-primary">
                                                        생성
                                                    </x-form-submit>
                                    </x-form-post>
                                </div>
                            </x-row>

                        </x-card-body>
                    </x-card>
                </div>
            </x-row>


        </x-container-fluid>
    </x-theme-layout>
</x-theme>
