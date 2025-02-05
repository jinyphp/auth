<div class="text-center mt-4">
    <h1 class="h2">비밀번호 변경</h1>
    <p class="lead"></p>
</div>

<!-- Validation Errors -->
@if ($errors->any())
    <div class="mb-4">
        <div class="font-medium text-red-600">
            {{ __('문제가 발생했습니다.') }}
        </div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">

        @includeIf('jiny-auth::password.reset.form')

        <div class="mt-3">
            <div class="text-center">
                <a class="font-medium text-indigo-600 hover:text-indigo-400" href="/login">로그인 이동</a>
            </div>
        </div>

    </div>
</div>
