<x-navtab class="mb-3 nav-bordered">

    <!-- Tab1 Active -->
    <x-navtab-item class="show active" >

        <x-navtab-link class="rounded-0 active">
            <span class="d-none d-md-block">구글 OAuth</span>
        </x-navtab-link>

        <p class="text-gray-500 text-sm mb-5">
            구글 로그인 연동 API 설정입니다.
        </p>

        <article class="space-y-6" >
            <div class="space-y-1">
                <label class="font-medium" for="google-oauth">OAuth</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="text" id="google-oauth" name="google_oauth"
                    model.defer="forms.google.enable">
            </div>
            <div class="space-y-1">
                <label class="font-medium" for="google-clinet-id">Clinet_id</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="password" id="google-clinet-id" name="google_clinet_id"
                    model.defer="forms.google.clinet_id">
            </div>
            <div class="space-y-1">
                <label class="font-medium" for="google-client-secret">Secret</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="password" id="google-client-secret" name="google_client_secret"
                    model.defer="forms.google.client_secret">

            </div>

            <div class="space-y-1">
                <div class="font-medium" for="google-client-secret">callback</div>
                <p>http://도메인/login/google/callback</p>
            </div>
        </article>

    </x-navtab-item>

    <!-- Tab2 -->
    <x-navtab-item >
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">페이스북</span>
        </x-navtab-link>
        <p class="text-gray-500 text-sm mb-5">
            페이스북 로그인 연동 API 설정입니다.
        </p>
        <div class="space-y-6" >
            <div class="space-y-1">
                <label class="font-medium" for="facebook-oauth">OAuth</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="text" id="facebook-oauth" name="facebook_oauth"
                    model.defer="forms.facebook.enable">

            </div>
            <div class="space-y-1">
                <label class="font-medium" for="facebook-clinet-id">Clinet_id</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="password" id="facebook-clinet-id" name="facebook_clinet_id"
                    model.defer="forms.facebook.clinet_id">
            </div>



            <div class="space-y-1">
                <label class="font-medium" for="facebook-client-secret">Secret</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="password" id="facebook-client-secret" name="facebook_client_secret"
                    model.defer="forms.facebook.client_secret">

            </div>

            <div class="space-y-1">
                <div class="font-medium" for="facebook-client-secret">callback</div>
                <p>http://도메인/login/facebook/callback</p>
            </div>

        </div>
    </x-navtab-item>

    <!-- Tab2 -->
    <x-navtab-item >
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">네이버</span>
        </x-navtab-link>
        <p class="text-gray-500 text-sm mb-5">
            네이버 로그인 연동 API 설정입니다.
        </p>
        <div class="space-y-6">
            <div class="space-y-1">
                <label class="font-medium" for="naver-oauth">OAuth</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="text" id="naver-oauth" name="naver_oauth"
                    model.defer="forms.naver.enable">

            </div>
            <div class="space-y-1">
                <label class="font-medium" for="naver-clinet-id">Clinet_id</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="password" id="naver-clinet-id" name="naver_clinet_id"
                    model.defer="forms.naver.clinet_id">
            </div>



            <div class="space-y-1">
                <label class="font-medium" for="naver-client-secret">Secret</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="password" id="naver-client-secret" name="naver_client_secret"
                    model.defer="forms.naver.client_secret">

            </div>

            <div class="space-y-1">
                <div class="font-medium" for="naver-client-secret">callback</div>
                <p>http://도메인/login/naver/callback</p>
            </div>


        </div>
    </x-navtab-item>

    <!-- Tab2 -->
    <x-navtab-item >
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">카카오</span>
        </x-navtab-link>
        <p class="text-gray-500 text-sm mb-5">
            카카오 로그인 연동 API 설정입니다.
        </p>
        <div class="space-y-6" >
            <div class="space-y-1">
                <label class="font-medium" for="kakao-oauth">OAuth</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="text" id="kakao-oauth" name="kakao_oauth"
                    model.defer="forms.kakao.enable">

            </div>
            <div class="space-y-1">
                <label class="font-medium" for="kakao-clinet-id">Clinet_id</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="password" id="kakao-clinet-id" name="kakao_clinet_id"
                    model.defer="forms.kakao.clinet_id">
            </div>



            <div class="space-y-1">
                <label class="font-medium" for="kakao-client-secret">Secret</label>
                <input
                    class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    type="password" id="kakao-client-secret" name="kakao_client_secret"
                    model.defer="forms.kakao.client_secret">

            </div>

            <div class="space-y-1">
                <div class="font-medium" for="kakao-client-secret">callback</div>
                <p>http://도메인/login/kakao/callback</p>
            </div>


        </div>
    </x-navtab-item>

</x-navtab>

