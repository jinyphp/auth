{{-- Login Component --}}
@guest
    {{-- 로그인되지 않은 상태에서만 표시 --}}
    <a href="/login" {{ $attributes->merge(['class' => 'btn']) }}>{{ $slot }}</a>
@endguest