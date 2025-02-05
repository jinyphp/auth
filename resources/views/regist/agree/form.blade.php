@if (session()->has('error'))
    <div class="font-medium text-red-600">
        {{ session('error') }}
    </div>
@endif

<form method="POST" action="{{ route('regist.agree') }}" class="space-y-6">
    @csrf

    @foreach ($agreement as $item)
        {{-- <p class="mb-3">{{ $item->content }}</p> --}}
        <div class="mb-3">

            <a class="text-decoration-none text-body mb-2"
                href="/terms/{{ $item->slug }}" target="_blank">
                {{ $item->title }}
            </a>

            <label class="form-check">
                <input class="form-check-input" type="checkbox" id="agree" name="agree[]" value="{{ $item->id }}">

                @if ($item->required)
                    <span class="form-check-label">
                        <span class="text-danger">* 필수</span> 동의 필요
                    </span>
                @else
                    <span class="form-check-label">
                        선택 동의 필요
                    </span>
                @endif

            </label>

            <hr class="my-0">

        </div>
    @endforeach

    <button type="submit" class="btn btn-primary w-100">
        {{ __('동의') }}
    </button>

</form>
