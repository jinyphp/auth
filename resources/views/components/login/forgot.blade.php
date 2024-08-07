@if (Route::has('password.request'))
    <a href="{{ route('password.request') }}"
        {{$attributes->merge(['class'=>"inline-block"])}}
        {{-- class="text-indigo-600 hover:text-indigo-400" --}}
        >
        {{$slot}}
    </a>
@endif
