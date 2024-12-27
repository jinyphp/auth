<div class="card flex-fill w-100 h-100">
    <div class="card-header">
        <div class="card-actions float-end">
            <div class="dropdown position-relative">
                <a href="#" data-bs-toggle="dropdown" data-bs-display="static">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" data-lucide="more-horizontal"
                        class="lucide lucide-more-horizontal align-middle">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="19" cy="12" r="1"></circle>
                        <circle cx="5" cy="12" r="1"></circle>
                    </svg>
                </a>

                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="/admin/locale/country">국가</a>
                    <a class="dropdown-item" href="/admin/locale/language">언어</a>
                    <a class="dropdown-item" href="#">통화</a>
                </div>
            </div>
        </div>
        <a href="/admin/locale" class="text-decoration-none">
            <h5 class="card-title mb-0">지역설정</h5>
        </a>
    </div>
    <div class="card-body p-2">
        <div id="world_map" style="height:350px;"></div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const map = new jsVectorMap({
                    selector: '#world_map',
                    map: 'world',
                    zoomButtons: true,

                    regionStyle: {
                        initial: {
                            fill: '#e4e4e4'
                        }
                    },

                    zoomOnScroll: false,

                    markers: [
                        // {
                        //     name: "대한민국",
                        //     coords: [37.5665, 126.9780] // 위도(latitude), 경도(longitude)
                        // },
                        // {
                        //     name: "미국",
                        //     coords: [38.8977, -77.0365]
                        // },
                        // {
                        //     name: "영국",
                        //     coords: [51.5074, -0.1278]
                        // }
                        @foreach (DB::table('country')->get() as $country)
                            {
                                name: "{{ $country->name }}",
                                coords: [{{ $country->latitude }}, {{ $country->longitude }}]
                            }
                            @if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                    ],

                    markerStyle: {
                        initial: {
                            fill: '#4680ff'
                        }
                    },

                    labels: {
                        markers: {
                            render: (marker) => marker.name
                        }
                    }
                });
            });
        </script>
    </div>

</div>
