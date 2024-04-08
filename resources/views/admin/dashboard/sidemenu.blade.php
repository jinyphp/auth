<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">인증처리</h5>
    </div>

    <div class="list-group list-group-flush" role="tablist">
        <a class="list-group-item list-group-item-action active"
            data-bs-toggle="list" href="#account" role="tab"
            aria-selected="true">
            회원정보
        </a>
        <a class="list-group-item list-group-item-action"
            data-bs-toggle="list" href="#password" role="tab" aria-selected="false" tabindex="-1">
            미인증회원
        </a>
        <a class="list-group-item list-group-item-action"
            data-bs-toggle="list" href="#" role="tab" aria-selected="false" tabindex="-1">
            휴먼회원
        </a>
        <a class="list-group-item list-group-item-action"
            data-bs-toggle="list" href="#" role="tab" aria-selected="false" tabindex="-1">
            예약됨
        </a>
        <a class="list-group-item list-group-item-action"
            data-bs-toggle="list" href="#" role="tab" aria-selected="false" tabindex="-1">
            블랙리스트
        </a>
        <a class="list-group-item list-group-item-action"
            data-bs-toggle="list" href="#" role="tab" aria-selected="false" tabindex="-1">
            역할
        </a>
        <a class="list-group-item list-group-item-action"
            data-bs-toggle="list" href="#" role="tab" aria-selected="false" tabindex="-1">
            등급
        </a>
        <a class="list-group-item list-group-item-action"
            data-bs-toggle="list" href="#" role="tab" aria-selected="false" tabindex="-1">
            국가
        </a>
        <a class="list-group-item list-group-item-action"
            href="/admin/auth/agreement">
            동의서
        </a>
        <a class="list-group-item list-group-item-action"
            data-bs-toggle="list"
            href="/admin/auth/password"
            role="tab" aria-selected="false" tabindex="-1">
            패스워드 관리
        </a>

    </div>
</div>

<!-- 연동설정 -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            소셜연동
        </h5>
    </div>
    {{--
    <div class="list-group list-group-flush" role="tablist">
        <a class="list-group-item list-group-item-action"
            href="/admin/auth/social">
            연동설정
        </a>
    </div>
    --}}

    <div class="px-2 border-top">
        @foreach (social_login_all() as $item)
        <div class="badge bg-primary my-2">
            <a href="/admin/auth/social"
                class="text-white"
                style="text-decoration: none;">
            {{$item}}
            </a>
        </div>
        @endforeach
    </div>
</div>

<!-- 설정 -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            환경설정
        </h5>
    </div>

    <div class="list-group list-group-flush" role="tablist">
        <a class="list-group-item list-group-item-action"
            href="/admin/auth/setting/login">
            로그인 설정
        </a>
        <a class="list-group-item list-group-item-action"
            href="/admin/auth/setting/register">
            회원가입
        </a>
        <a class="list-group-item list-group-item-action"
            href="/admin/auth/settings">
            로그인 관리
        </a>
    </div>
</div>
