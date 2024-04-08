@php
    $provider = DB::table('user_oauth_providers')->where('enable', 1)->get();
@endphp

@if (count($provider) > 0)
    <!-- Divider: With Heading -->
    <h3 class="flex items-center my-8">
        <span aria-hidden="true" class="grow bg-gray-200 rounded h-0.5"></span>
        <span class="text-sm font-medium mx-3">소셜 로그인</span>
        <span aria-hidden="true" class="grow bg-gray-200 rounded h-0.5"></span>
    </h3>
    <!-- END Divider: With Heading -->

    {{-- Social Login Link --}}
    <div class="flex justify-center items-center space-x-4">
        <div>
            @foreach ($provider as $item)
                <a href="{{ route('oauth-redirect', $item->name) }}">
                    {{ $item->name }}
                </a>
            @endforeach
        </div>
    </div>
@endif
