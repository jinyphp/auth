<x-www-app>
    {{-- page-center --}}
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">

                <div class="col-sm-10 col-md-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">
                        <section>


                            @includeIf('jiny-auth::password.forget.main')





                        </section>
                    </div>
                </div>

            </div>
        </div>
    </main>
</x-www-app>
