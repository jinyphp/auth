<x-admin-hyper>
    <div class="container-xl mx-auto p-4">
        <!-- For large screens, apply additional padding -->
        <div class="p-lg-8">
          <!-- Content goes here -->
          <div class="my-4 my-lg-8">

            <!-- Overview -->
            <div class="row align-items-center justify-content-between border-bottom border-gray-200 mb-4 mb-lg-8">
                <div class="col-sm">
                    <h2 class="text-xl font-weight-bold py-2">{{$actions['title']}}</h2>
                </div>
                <div class="col-sm mt-3 mt-sm-0 text-center text-sm-right">
                    <select class="form-select border border-gray-200 rounded-sm text-sm px-2 py-1 w-100 border-blue-500 focus-ring-blue-500 focus-ring-opacity-50">
                        <option>Today</option>
                        <option selected>This Week</option>
                        <option>This Month</option>
                        <option>This Year</option>
                    </select>
                </div>
            </div>
            <!-- END Overview -->



            <!-- Statistics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-8">
                <!-- Sales -->
                <div class="flex flex-col rounded shadow-sm bg-white overflow-hidden">
                    <div class="p-5 lg:p-6 flex-grow w-full flex justify-between items-center">
                        <dl>
                            <dt class="text-2xl font-semibold">
                                {{ $newUser }}

                            </dt>
                            <dd class="uppercase font-medium text-sm text-gray-500 tracking-wider">
                                전체회원 {{ $total_count }}
                            </dd>
                        </dl>
                        <div
                            class="font-semibold inline-flex px-2 py-1 leading-4 items-center space-x-1 text-sm rounded-full text-emerald-700 bg-emerald-200">
                            <svg class="hi-solid hi-arrow-circle-up inline-block w-4 h-4" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>2.3%</span>
                        </div>
                    </div>
                    <a href="/admin/auth/users"
                        class="block p-3 font-medium text-sm text-center bg-gray-50 hover:bg-gray-100 hover:bg-opacity-75 active:bg-gray-50 text-blue-600 hover:text-blue-500">
                        회원목록
                    </a>
                </div>
                <!-- END Sales -->

                <!-- Contacts -->
                <div class="flex flex-col rounded shadow-sm bg-white overflow-hidden">
                    <div class="p-5 lg:p-6 flex-grow w-full flex justify-between items-center">
                        <dl>
                            <dt class="text-2xl font-semibold">
                                3
                            </dt>
                            <dd class="uppercase font-medium text-sm text-gray-500 tracking-wider">
                                활성 접속자
                            </dd>
                        </dl>
                        <div
                            class="font-semibold inline-flex px-2 py-1 leading-4 items-center space-x-1 text-sm rounded-full text-emerald-700 bg-emerald-200">
                            <svg class="hi-solid hi-arrow-circle-up inline-block w-4 h-4" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>1.3%</span>
                        </div>
                    </div>

                    <a href="/admin/auth/logs"
                        class="block p-3 font-medium text-sm text-center bg-gray-50 hover:bg-gray-100 hover:bg-opacity-75 active:bg-gray-50 text-blue-600 hover:text-blue-500">
                        접속로그
                    </a>
                </div>
                <!-- END Contacts -->

                <!-- Earnings -->
                <div class="flex flex-col rounded shadow-sm bg-white overflow-hidden">
                    <div class="p-5 lg:p-6 flex-grow w-full flex justify-between items-center">
                        <dl>
                            <dt class="text-2xl font-semibold">
                                미인증 {{ $auth_count }} 명
                            </dt>
                            <dd class="uppercase font-medium text-sm text-gray-500 tracking-wider">
                                미승인 {{ $total_count - $auth_count }} /
                                활성회원 {{ $total_count - $sleeper_count }}
                            </dd>
                        </dl>
                        <div
                            class="font-semibold inline-flex px-2 py-1 leading-4 items-center space-x-1 text-sm rounded-full text-red-700 bg-red-200">
                            <svg class="hi-solid hi-arrow-circle-down inline-block w-4 h-4" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>0.5%</span>
                        </div>
                    </div>
                    <a href="javascript:void(0)"
                        class="block p-3 font-medium text-sm text-center bg-gray-50 hover:bg-gray-100 hover:bg-opacity-75 active:bg-gray-50 text-blue-600 hover:text-blue-500">
                        View all Earnings
                    </a>
                </div>
                <!-- END Earnings -->

                <!-- Wallet -->
                <div class="flex flex-col rounded shadow-sm bg-white overflow-hidden">
                    <div class="p-5 lg:p-6 flex-grow w-full flex justify-between items-center">
                        <dl>
                            <dt class="text-2xl font-semibold">
                                {{ $sleeper_count }}명
                            </dt>
                            <dd class="uppercase font-medium text-sm text-gray-500 tracking-wider">
                                휴먼회원
                            </dd>
                        </dl>
                        <div
                            class="font-semibold inline-flex px-2 py-1 leading-4 items-center space-x-1 text-sm rounded-full text-emerald-700 bg-emerald-200">
                            <svg class="hi-solid hi-arrow-circle-up inline-block w-4 h-4" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>3.5%</span>
                        </div>
                    </div>
                    <a href="javascript:void(0)"
                        class="block p-3 font-medium text-sm text-center bg-gray-50 hover:bg-gray-100 hover:bg-opacity-75 active:bg-gray-50 text-blue-600 hover:text-blue-500">
                        Check your Balance
                    </a>
                </div>
                <!-- END Wallet -->

                <!-- Sales -->
                <div class="md:col-span-2 flex flex-col rounded shadow-sm bg-white overflow-hidden">
                    <div class="py-4 px-5 lg:px-6 w-full bg-gray-50">
                        <h3 class="font-medium">
                            Sales
                        </h3>
                    </div>
                    <div class="p-5 flex-grow w-full flex justify-between items-center">
                        <!-- Sales Chart Container -->
                        <!-- Chart.js Chart is initialized in resources/js/app/01-crm/dashboard.js -->
                        <!-- For more info and examples you can check out http://www.chartjs.org/docs/ -->
                        <canvas id="js-chartjs-sales" width="828" height="414"
                            style="display: block; box-sizing: border-box; height: 276px; width: 552px;"></canvas>
                    </div>
                </div>
                <!-- END Sales -->

                <!-- Earnings -->
                <div class="md:col-span-2 flex flex-col rounded shadow-sm bg-white overflow-hidden">
                    <div class="py-4 px-5 lg:px-6 w-full bg-gray-50">
                        <h3 class="font-medium">
                            Earnings
                        </h3>
                    </div>
                    <div class="p-5 flex-grow w-full flex justify-between items-center">
                        <!-- Earnings Chart Container -->
                        <!-- Chart.js Chart is initialized in resources/js/app/01-crm/dashboard.js -->
                        <!-- For more info and examples you can check out http://www.chartjs.org/docs/ -->
                        <canvas id="js-chartjs-earnings" width="828" height="414"
                            style="display: block; box-sizing: border-box; height: 276px; width: 552px;"></canvas>
                    </div>
                </div>
                <!-- END Earnings -->
            </div>
            <!-- END Statistics -->




            <!-- Open Deals -->
            <h2 class="text-xl font-bold py-2 border-b-2 border-gray-200 mb-4 lg:mb-8">
                Pending Deals
            </h2>
            <!-- END Open Deals -->

            <!-- Open Deals -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-8">
                <a href="javascript:void(0)"
                    class="flex flex-col rounded transition shadow-sm hover:shadow-xl hover:shadow-gray-200 active:shadow-sm bg-white overflow-hidden">
                    <div class="flex flex-col items-center justify-center bg-gray-50 py-6">
                        <div class="rounded-full bg-orange-300 p-1"><img
                                src="https://source.unsplash.com/BGz8vO3pK8k/160x160" alt="User Avatar"
                                class="inline-block w-20 h-20 rounded-full"></div>
                        <div class="text-center mt-3">
                            <h3 class="text-lg font-semibold">
                                Zoya Perez
                            </h3>
                            <p class="text-gray-600 text-sm">
                                Web Developer
                            </p>
                        </div>
                    </div>
                    <div class="p-5 lg:p-6">
                        <ul class="border border-gray-200 rounded bg-white divide-y divide-gray-200">
                            <li class="p-4 flex justify-between items-center bg-gray-50">
                                <span class="font-semibold text-sm mr-1">Plan</span>
                                <span class="text-blue-500 text-sm font-medium">Freelancer ($500/year)</span>
                            </li>
                            <li class="p-4 flex justify-between items-center">
                                <span class="font-semibold text-sm mr-1">Contact</span>
                                <span class="text-emerald-500 text-sm">Added</span>
                            </li>
                            <li class="p-4 flex justify-between items-center">
                                <span class="font-semibold text-sm mr-1">Reached out</span>
                                <span class="text-emerald-500 text-sm">Email</span>
                            </li>
                            <li class="p-4 flex justify-between items-center">
                                <span class="font-semibold text-sm mr-1">Response</span>
                                <span class="text-orange-500 text-sm">Pending</span>
                            </li>
                        </ul>
                    </div>
                </a>
                <a href="javascript:void(0)"
                    class="flex flex-col rounded transition shadow-sm hover:shadow-xl hover:shadow-gray-200 active:shadow-sm bg-white overflow-hidden">
                    <div class="flex flex-col items-center justify-center bg-gray-50 py-6">
                        <div class="rounded-full bg-orange-300 p-1"><img
                                src="https://source.unsplash.com/iFgRcqHznqg/160x160" alt="User Avatar"
                                class="inline-block w-20 h-20 rounded-full"></div>
                        <div class="text-center mt-3">
                            <h3 class="text-lg font-semibold">
                                Xavier Rosales
                            </h3>
                            <p class="text-gray-600 text-sm">
                                Web Designer
                            </p>
                        </div>
                    </div>
                    <div class="p-5 lg:p-6">
                        <ul class="border border-gray-200 rounded bg-white divide-y divide-gray-200">
                            <li class="p-4 flex justify-between items-center bg-gray-50">
                                <span class="font-semibold text-sm mr-1">Plan</span>
                                <span class="text-blue-500 text-sm font-medium">Freelancer ($500/year)</span>
                            </li>
                            <li class="p-4 flex justify-between items-center">
                                <span class="font-semibold text-sm mr-1">Contact</span>
                                <span class="text-emerald-500 text-sm">Added</span>
                            </li>
                            <li class="p-4 flex justify-between items-center">
                                <span class="font-semibold text-sm mr-1">Reached out</span>
                                <span class="text-orange-500 text-sm">Not yet</span>
                            </li>
                            <li class="p-4 flex justify-between items-center">
                                <span class="font-semibold text-sm mr-1">Response</span>
                                <span class="text-orange-500 text-sm">Not yet</span>
                            </li>
                        </ul>
                    </div>
                </a>
                <a href="javascript:void(0)"
                    class="flex flex-col rounded transition shadow-sm hover:shadow-xl hover:shadow-gray-200 active:shadow-sm bg-white overflow-hidden">
                    <div class="flex flex-col items-center justify-center bg-gray-50 py-6">
                        <div class="rounded-full bg-orange-300 p-1"><img
                                src="https://source.unsplash.com/mEZ3PoFGs_k/160x160" alt="User Avatar"
                                class="inline-block w-20 h-20 rounded-full"></div>
                        <div class="text-center mt-3">
                            <h3 class="text-lg font-semibold">
                                Nansi Hart
                            </h3>
                            <p class="text-gray-600 text-sm">
                                CEO
                            </p>
                        </div>
                    </div>
                    <div class="p-5 lg:p-6">
                        <ul class="border border-gray-200 rounded bg-white divide-y divide-gray-200">
                            <li class="p-4 flex justify-between items-center bg-gray-50">
                                <span class="font-semibold text-sm mr-1">Plan</span>
                                <span class="text-blue-500 text-sm font-medium">Agency ($1560/year)</span>
                            </li>
                            <li class="p-4 flex justify-between items-center">
                                <span class="font-semibold text-sm mr-1">Contact</span>
                                <span class="text-emerald-500 text-sm">Added</span>
                            </li>
                            <li class="p-4 flex justify-between items-center">
                                <span class="font-semibold text-sm mr-1">Reached out</span>
                                <span class="text-emerald-500 text-sm">Email</span>
                            </li>
                            <li class="p-4 flex justify-between items-center">
                                <span class="font-semibold text-sm mr-1">Response</span>
                                <span class="text-orange-500 text-sm">Pending</span>
                            </li>
                        </ul>
                    </div>
                </a>
            </div>
            <!-- END Open Deals -->
        </div>
        </div>
    </div>




    {{-- SuperAdmin Actions Setting --}}
    @if(Module::has('Actions'))
        @livewire('setActionRule', ['actions'=>$actions])
    @endif
</x-admin-hyper>
