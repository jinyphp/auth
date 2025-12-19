@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '국가 관리')

@push('styles')
    <!-- jsvectormap CSS (Vector Map) -->
    <link href="{{ asset('assets/libs/jsvectormap/dist/jsvectormap.min.css') }}" rel="stylesheet" />
    <style>
        #countryMap {
            height: 400px;
            width: 100%;
            border-radius: 8px;
        }
        /* jsvectormap 컨테이너 스타일 */
        .jvm-container {
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        /* 마커 스타일 커스터마이징 */
        .jvm-marker {
            cursor: pointer;
        }
        .table th {
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
        }
        /* 컬럼 폭 조정 */
        .table th:nth-child(1), .table td:nth-child(1) { width: 8%; }  /* 코드 */
        .table th:nth-child(2), .table td:nth-child(2) { width: 6%; }  /* 이모지 */
        .table th:nth-child(3), .table td:nth-child(3) { width: 15%; } /* 국가명 */
        .table th:nth-child(4), .table td:nth-child(4) { width: 20%; } /* 설명 */
        .table th:nth-child(5), .table td:nth-child(5) { width: 8%; }  /* 상태 */
        .table th:nth-child(6), .table td:nth-child(6) { width: 10%; } /* 회원수 */
        .table th:nth-child(7), .table td:nth-child(7) { width: 15%; } /* 작업 */
    </style>
@endpush

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            국가 관리
                            <span class="fs-5">(총 {{ $countries->total() }}개)</span>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                <li class="breadcrumb-item">설정</li>
                                <li class="breadcrumb-item active">국가</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('admin.auth.user.countries.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>새 국가 추가
                    </a>
                </div>
            </div>
        </div>

        <!-- 지도 섹션 -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fe fe-map me-2"></i>국가 위치 지도
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="countryMap"></div>
                        <small class="text-muted mt-2 d-block">
                            <i class="fe fe-info me-1"></i>
                            위도와 경도가 설정된 국가들이 지도에 표시됩니다.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="search" name="search" class="form-control"
                                           placeholder="국가명 또는 코드 검색..."
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fe fe-search"></i> 검색
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>코드</th>
                                    <th>이모지</th>
                                    <th>국가명</th>
                                    <th>설명</th>
                                    <th>상태</th>
                                    <th>회원수</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($countries as $country)
                                <tr>
                                    <td><code>{{ $country->code }}</code></td>
                                    <td class="text-center">{{ $country->emoji }}</td>
                                    <td><strong>{{ $country->name }}</strong></td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $country->description }}">
                                            {{ $country->description ?: '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($country->enable)
                                            <span class="badge bg-success">활성</span>
                                        @else
                                            <span class="badge bg-secondary">비활성</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ number_format($country->users ?? 0) }}명</span>
                                    </td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <a href="{{ route('admin.auth.user.countries.show', $country->id) }}" class="btn btn-sm btn-light" title="상세 보기">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.auth.user.countries.edit', $country->id) }}" class="btn btn-sm btn-light" title="수정">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.auth.user.countries.destroy', $country->id) }}" method="POST" class="d-inline" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger" title="삭제">
                                                    <i class="fe fe-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">등록된 국가가 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($countries->hasPages())
                    <div class="card-footer">
                        {{ $countries->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <!-- jsvectormap Core Library (Vector Map) -->
    <script src="{{ asset('assets/libs/jsvectormap/dist/jsvectormap.min.js') }}"></script>
    <!-- World Map Data -->
    <script src="{{ asset('assets/libs/jsvectormap/dist/maps/world.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 국가 데이터를 지도에 마커로 표시 (위도/경도가 있는 모든 국가)
            const countries = @json($mapCountries ?? []);

            // 마커 데이터 준비 (jsvectormap은 [위도, 경도] 순서를 사용)
            const markers = [];
            countries.forEach(function(country) {
                if (country.latitude && country.longitude) {
                    const lat = parseFloat(country.latitude);
                    const lng = parseFloat(country.longitude);

                    // 마커 정보 구성 (이모지를 이름에 포함)
                    markers.push({
                        name: (country.emoji || '📍') + ' ' + (country.name || ''),
                        coords: [lat, lng], // jsvectormap은 [위도, 경도] 순서 사용
                        country: country // 전체 국가 정보를 저장하여 툴팁에서 사용
                    });
                }
            });

            // jsvectormap 지도 초기화
            const map = new jsVectorMap({
                map: 'world',
                selector: '#countryMap',
                zoomOnScroll: true,
                zoomButtons: true,
                markersSelectable: true,
                showTooltip: true,
                // 마커 스타일 설정
                markerStyle: {
                    initial: {
                        fill: '#007bff',
                        stroke: '#ffffff',
                        strokeWidth: 3,
                        r: 8
                    },
                    hover: {
                        fill: '#0056b3',
                        cursor: 'pointer'
                    },
                    selected: {
                        fill: '#0056b3'
                    }
                },
                // 마커 레이블 스타일
                markerLabelStyle: {
                    initial: {
                        fontFamily: 'Verdana',
                        fontSize: 12,
                        fontWeight: 500,
                        cursor: 'default',
                        fill: '#374151'
                    },
                    hover: {
                        cursor: 'pointer'
                    }
                },
                // 마커 레이블 표시 (국가명만 표시, 이모지는 툴팁에 표시)
                labels: {
                    markers: {
                        render: function(marker) {
                            // 마커 이름에서 이모지 제거하고 국가명만 반환
                            return marker.name.replace(/[\u{1F300}-\u{1F9FF}]/gu, '').trim();
                        }
                    }
                },
                // 마커 데이터
                markers: markers,
                // 마커 클릭 이벤트 처리
                onMarkerClick: function(event, index) {
                    const country = markers[index].country;
                    if (country) {
                        // 클릭 시 상세 페이지로 이동하거나 추가 정보 표시
                        console.log('마커 클릭:', country.name);
                    }
                },
                // 마커 툴팁 표시 이벤트 (호버 시)
                onMarkerTooltipShow: function(event, tooltip, index) {
                    const country = markers[index].country;
                    if (country) {
                        // 툴팁 내용 구성: 국가 정보 표시
                        const tooltipText =
                            '<div class="text-center" style="min-width: 150px; padding: 4px;">' +
                            '<h6 class="mb-1 fw-bold" style="font-size: 14px;">' + (country.emoji || '📍') + ' ' + (country.name || '') + '</h6>' +
                            '<p class="mb-1" style="font-size: 11px;"><code>' + (country.code || '') + '</code></p>' +
                            (country.description ? '<p class="mb-1" style="font-size: 11px; color: #6c757d;">' + country.description.substring(0, 50) + (country.description.length > 50 ? '...' : '') + '</p>' : '') +
                            '<p class="mb-0"><span class="badge bg-info" style="font-size: 10px;">' + (country.users || 0) + '명</span></p>' +
                            '</div>';
                        // 툴팁 텍스트 설정 (HTML 허용)
                        tooltip.text(tooltipText, true);
                    }
                }
            });
        });
    </script>
@endpush
