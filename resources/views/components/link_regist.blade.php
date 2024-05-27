
@auth

@else
<a {{ $attributes->merge(['href'=>"/regist"]) }}>
    {{$slot}}
</a>
@endauth
