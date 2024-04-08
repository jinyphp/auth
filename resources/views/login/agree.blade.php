<x-app>
    <x-bootstrap>
        <x-page-center>
            <!-- -->
            <div class="text-center mt-4">
                <h1 class="h2">약관동의</h1>
                <p class="lead">회원 가입을 위해서는 먼저 사전 약관에 동의가 필요합니다.</p>
            </div>

            <div class="card">
                <div class="card-body">

                    @if (session()->has('error'))
                        <div class="font-medium text-red-600">
                            {{session('error')}}
                        </div>
                    @endif


                    <form method="POST" action="{{ route('agreement') }}" class="space-y-6">
                        @csrf

                        @foreach($agreement as $item)
                        <p class="mb-3">{{$item->content}}</p>
                        <div class="mb-3">
                            <label class="form-check">
                                <input class="form-check-input"
                                type="checkbox" id="agree" name="agree[]" value="{{$item->id}}">
                                <span class="form-check-label">
                                    {{$item->title}}
                                </span>
                            </label>
                        </div>
                        @endforeach

                        <button type="submit" class="btn btn-lg btn-primary">
                            {{ __('Agree') }}
                        </button>


                        <div class="row">
                            <div class="col">
                                <hr>
                            </div>
                            <div class="col-auto text-uppercase d-flex align-items-center">Or</div>
                            <div class="col">
                                <hr>
                            </div>
                        </div>

                        <small>
                            <a href="{{ route('login') }}">{{ __('회원이시면 로그인을 해주세요.') }}</a>
                        </small>


                    </form>

                </div>
            </div>
            <div class="text-center mb-3">
                Copyright all right reserved JinyPHP
            </div>

            <!-- -->
        </x-page-center>
    </x-bootstrap>
</x-app>
