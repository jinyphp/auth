@guest
<div {{ $attributes->merge(['class' => 'd-flex gap-2 align-items-center lh-0 d-none d-md-block']) }}>
    <span>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-person-fill" viewBox="0 0 16 16">
            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
        </svg>
    </span>
    <a href="/register" class="text-inherit fs-5 fw-medium">{{ $slot ?: 'Signup' }}</a>
</div>
@endguest