<x-www-app>
    {{-- page-center --}}
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">

                        <section>
                            <header>
                                <div class="d-flex justify-content-between align-items-end mb-3">
                                    <h1 class="h2 mb-0">오류</h1>

                                    <a class="nav-link text-decoration-underline p-0 ms-2" href="/login">
                                        로그인
                                    </a>
                                </div>
                            </header>

                            @if(isset($message))
                                @if(!is_array($message))
                                <div class="text-center">
                                    {{$message}}
                                </div>
                                @else
                                <ul>
                                    @foreach($message as $item)
                                    <li>{{$item}}</li>
                                    @endforeach
                                </ul>
                                @endif
                            @endif

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
                        </section>

                        <!-- -->
                    </div>
                </div>
            </div>
        </div>
    </main>



</x-www-app>
