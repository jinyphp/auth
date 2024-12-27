<x-www-app>
    {{-- page-center --}}
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">

                        <section>
                            <header>
                                <div class="d-flex justify-content-between align-items-end mb-3">
                                    <h1 class="h2 mb-0">약관동의</h1>

                                    <a class="nav-link text-decoration-underline p-0 ms-2" href="/login">
                                        로그인
                                    </a>
                                </div>

                                <div class="fs-sm mb-4">
                                    {{ __('회원 가입을 위해서는 먼저 사전 약관에 동의가 필요합니다.') }}

                                </div>
                            </header>


                            @if (session()->has('error'))
                                <div class="font-medium text-red-600">
                                    {{ session('error') }}
                                </div>
                            @endif



                            <form method="POST"
                                action="{{ route('agreement') }}"
                                class="space-y-6">
                                @csrf

                                @foreach ($agreement as $item)

                                    {{-- <p class="mb-3">{{ $item->content }}</p> --}}
                                    <div class="mb-3">
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox" id="agree"
                                                name="agree[]" value="{{ $item->id }}">
                                            <span class="form-check-label">
                                                <a href="/terms/{{ $item->slug }}"
                                                    class="text-decoration-none text-body">
                                                    {{ $item->title }}
                                                </a>

                                            </span>
                                        </label>
                                    </div>

                                @endforeach

                                <button type="submit" class="btn btn-primary w-100">
                                    {{ __('동의') }}
                                </button>
                            </form>




                            <!-- Footer -->
                            <footer class="mt-4">
                                <div class="nav mb-4">
                                    <a class="nav-link text-decoration-underline p-0" href="/support/help">도움이
                                        필요하신가요?</a>
                                </div>
                                <p class="fs-xs mb-0">
                                    &copy; All rights reserved.
                                    Made by <span class="animate-underline"><a
                                            class="animate-target text-dark-emphasis text-decoration-none"
                                            href="https://jinyphp.com/" target="_blank" rel="noreferrer">jinyphp</a>
                                    </span>
                                </p>
                            </footer>
                            <!-- end of footer -->

                        </section>

                        <!-- -->
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-www-app>
