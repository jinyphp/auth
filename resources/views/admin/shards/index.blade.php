@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '샤드 테이블 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">샤드 테이블 관리</h2>
                    <p class="text-muted mb-0">대용량 데이터 처리를 위한 샤딩 테이블 관리</p>
                </div>
                <div class="d-flex gap-2">
                    @if($shardTables->count() > 0)
                        <!-- 모든 샤드 삭제 -->
                        <form action="{{ route('admin.auth.shards.reset-all-tables') }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-danger"
                                    onclick="return confirm('모든 샤드 테이블의 샤드를 삭제하시겠습니까?\n이 작업은 되돌릴 수 없습니다!')">
                                <i class="bi bi-trash"></i> 모든 샤드 삭제
                            </button>
                        </form>
                        <!-- 모든 샤드 생성 -->
                        <form action="{{ route('admin.auth.shards.create-all-tables') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit"
                                    class="btn btn-success"
                                    onclick="return confirm('모든 샤드 테이블의 샤드를 생성하시겠습니까?')">
                                <i class="bi bi-database-add"></i> 모든 샤드 생성
                            </button>
                        </form>
                    @endif
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTableModal">
                        <i class="bi bi-plus-circle"></i> 샤드 테이블 추가
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- 샤드 테이블 그리드 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">등록된 샤드 테이블</h5>
                </div>
                <div class="card-body">
                    @if($shardTables->count() > 0)
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                            @foreach($shardTables as $table)
                                <div class="col">
                                    <div class="card h-100 {{ $selectedTable && $selectedTable->id === $table->id ? 'border-primary' : '' }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title mb-0">
                                                    <i class="bi bi-database text-primary"></i>
                                                    {{ $table->table_name }}
                                                </h5>
                                                <div class="d-flex flex-column gap-1 align-items-end">
                                                    <span class="badge {{ $table->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $table->is_active ? '활성' : '비활성' }}
                                                    </span>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input shard-toggle"
                                                               type="checkbox"
                                                               data-table-id="{{ $table->id }}"
                                                               {{ $table->sharding_enabled ? 'checked' : '' }}>
                                                        <label class="form-check-label small">
                                                            샤딩
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="card-text text-muted small mb-3">
                                                {{ $table->description ?: '설명 없음' }}
                                            </p>
                                            <div class="mb-3">
                                                <small class="d-block">
                                                    <strong>샤드 수:</strong> {{ $table->shard_count }}개
                                                </small>
                                                <small class="d-block">
                                                    <strong>샤딩 키:</strong> {{ $table->shard_key }}
                                                </small>
                                                <small class="d-block">
                                                    <strong>접두사:</strong> {{ $table->table_prefix ?: $table->table_name . '_' }}
                                                </small>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="?table={{ $table->table_name }}" class="btn btn-sm btn-primary flex-fill">
                                                    <i class="bi bi-eye"></i> 보기
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editTableModal{{ $table->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('admin.auth.shards.tables.delete', $table->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('이 샤드 테이블과 생성된 모든 샤드를 삭제하시겠습니까?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 수정 모달 -->
                                <div class="modal fade" id="editTableModal{{ $table->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.auth.shards.tables.update', $table->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">샤드 테이블 수정</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">테이블명 <span class="text-danger">*</span></label>
                                                        <input type="text" name="table_name" class="form-control" value="{{ $table->table_name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">테이블 접두사</label>
                                                        <input type="text" name="table_prefix" class="form-control" value="{{ $table->table_prefix }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">설명</label>
                                                        <input type="text" name="description" class="form-control" value="{{ $table->description }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">샤드 개수 <span class="text-danger">*</span></label>
                                                        <input type="number" name="shard_count" class="form-control" value="{{ $table->shard_count }}" required min="1" max="100">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">샤딩 키 <span class="text-danger">*</span></label>
                                                        <input type="text" name="shard_key" class="form-control" value="{{ $table->shard_key }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active{{ $table->id }}" {{ $table->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_active{{ $table->id }}">
                                                                활성화
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="sharding_enabled" value="1" id="sharding_enabled{{ $table->id }}" {{ $table->sharding_enabled ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="sharding_enabled{{ $table->id }}">
                                                                샤딩 활성화
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                                                    <button type="submit" class="btn btn-primary">수정</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">등록된 샤드 테이블이 없습니다. 먼저 샤드 테이블을 추가해주세요.</p>
                    @endif
                </div>
            </div>

            @if($selectedTable)
                <!-- 구분선 -->
                <hr class="my-4" style="border-top: 1px solid #dee2e6; opacity: 0.5;">

                <!-- 선택된 테이블의 샤드 관리 -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <i class="bi bi-database text-primary"></i>
                        {{ $selectedTable->table_name }} 샤드 관리
                    </h4>
                    <div class="d-flex gap-2">
                        @if(isset($statistics) && $statistics['active_shards'] > 0)
                            <form action="{{ route('admin.auth.shards.reset') }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="table_id" value="{{ $selectedTable->id }}">
                                <button type="submit"
                                        class="btn btn-danger"
                                        onclick="return confirm('모든 샤드 테이블을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')">
                                    <i class="bi bi-trash"></i> 초기화
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('admin.auth.shards.create-all') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="table_id" value="{{ $selectedTable->id }}">
                            <button type="submit"
                                    class="btn btn-primary"
                                    @if(!$selectedTable->sharding_enabled) disabled title="샤딩이 비활성화되어 있습니다"
                                    @elseif(isset($statistics) && $statistics['active_shards'] >= $statistics['total_shards']) disabled @endif
                                    onclick="return confirm('모든 샤드 테이블을 생성하시겠습니까?')">
                                <i class="bi bi-plus-circle"></i> 모든 샤드 생성
                            </button>
                        </form>
                    </div>
                </div>

                <!-- 통계 카드 -->
                @if($statistics)
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">총 샤드 수</h5>
                                <h2 class="mb-0">{{ $statistics['total_shards'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">활성 샤드</h5>
                                <h2 class="mb-0">{{ $statistics['active_shards'] ?? 0 }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">총 레코드</h5>
                                <h2 class="mb-0">{{ number_format($statistics['total_records'] ?? 0) }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 샤드 목록 -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">샤드 테이블 목록</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>샤드 ID</th>
                                    <th>테이블명</th>
                                    <th>상태</th>
                                    <th>사용자 수</th>
                                    <th>액션</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shards as $shard)
                                    <tr>
                                        <td><strong>{{ $shard['shard_id'] }}</strong></td>
                                        <td>
                                            @if($shard['exists'])
                                                <a href="#" class="text-primary text-decoration-none show-schema-link"
                                                   data-table="{{ $shard['table_name'] }}"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#schemaModal">
                                                    <code>{{ $shard['table_name'] }}</code>
                                                    <i class="bi bi-info-circle ms-1"></i>
                                                </a>
                                            @else
                                                <code class="text-muted">{{ $shard['table_name'] }}</code>
                                            @endif
                                        </td>
                                        <td>
                                            @if($shard['exists'])
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> 활성
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-x-circle"></i> 0명
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($shard['exists'])
                                                @php
                                                    $totalRecords = $statistics['total_records'] ?? 0;
                                                    $recordCount = $shard['record_count'] ?? 0;
                                                    $percentage = $totalRecords > 0 ? round(($recordCount / $totalRecords) * 100, 1) : 0;
                                                @endphp
                                                <span class="text-dark">{{ number_format($recordCount) }}명</span>
                                                <small class="text-muted ms-1">({{ $percentage }}%)</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$shard['exists'])
                                                <button type="button" class="btn btn-sm btn-outline-primary" disabled>
                                                    <i class="bi bi-people"></i> 사용자 없음
                                                </button>
                                            @else
                                                @if($shard['record_count'] > 0)
                                                    <a href="{{ route('admin.auth.users.shard', $shard['shard_id']) }}" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-people"></i> 사용자 보기
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="bi bi-people"></i> 사용자 없음
                                                    </button>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- 샤드 테이블 추가 모달 -->
<div class="modal fade" id="addTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.auth.shards.tables.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">샤드 테이블 추가</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">테이블명 <span class="text-danger">*</span></label>
                        <input type="text" name="table_name" class="form-control" required
                               placeholder="예: users, profiles, addresses">
                        <small class="text-muted">샤딩할 기본 테이블명을 입력하세요</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">테이블 접두사</label>
                        <input type="text" name="table_prefix" class="form-control"
                               placeholder="예: users_ (비워두면 '테이블명_'이 사용됩니다)">
                        <small class="text-muted">샤드 테이블 접두사 (예: users_001, users_002...)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">설명</label>
                        <input type="text" name="description" class="form-control"
                               placeholder="예: 회원 정보 샤딩 테이블">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">샤드 개수 <span class="text-danger">*</span></label>
                        <input type="number" name="shard_count" class="form-control" value="10" required min="1" max="100">
                        <small class="text-muted">생성할 샤드 테이블 개수 (1-100)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">샤딩 키 <span class="text-danger">*</span></label>
                        <input type="text" name="shard_key" class="form-control" value="uuid" required>
                        <small class="text-muted">샤딩 기준 컬럼 (예: uuid, user_uuid)</small>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="sharding_enabled" value="1" id="new_sharding_enabled" checked>
                            <label class="form-check-label" for="new_sharding_enabled">
                                샤딩 활성화
                            </label>
                        </div>
                    </div>
                    <input type="hidden" name="strategy" value="hash">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">추가</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 스키마 정보 모달 -->
<div class="modal fade" id="schemaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-table"></i>
                    테이블 스키마: <span id="schemaTableName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="schemaLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">로딩 중...</span>
                    </div>
                </div>
                <div id="schemaContent" style="display: none;">
                    <div class="mb-3">
                        <small class="text-muted">
                            데이터베이스: <strong id="schemaDriver"></strong>
                        </small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>컬럼명</th>
                                    <th>타입</th>
                                    <th>NULL</th>
                                    <th>기본값</th>
                                    <th>키</th>
                                </tr>
                            </thead>
                            <tbody id="schemaTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="schemaError" class="alert alert-danger" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    function showTableSchema(tableName) {
        // 모달 초기화
        document.getElementById('schemaTableName').textContent = tableName;
        document.getElementById('schemaLoading').style.display = 'block';
        document.getElementById('schemaContent').style.display = 'none';
        document.getElementById('schemaError').style.display = 'none';

        // AJAX 요청
        fetch(`{{ route('admin.auth.shards.schema') }}?table_name=${encodeURIComponent(tableName)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('schemaLoading').style.display = 'none';

                if (data.error) {
                    document.getElementById('schemaError').textContent = data.error;
                    document.getElementById('schemaError').style.display = 'block';
                    return;
                }

                // 스키마 정보 표시
                document.getElementById('schemaDriver').textContent = data.driver.toUpperCase();
                const tbody = document.getElementById('schemaTableBody');
                tbody.innerHTML = '';

                data.columns.forEach(column => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><code>${column.name}</code></td>
                        <td>${column.type}</td>
                        <td>${column.nullable ? '<span class="badge bg-success">YES</span>' : '<span class="badge bg-secondary">NO</span>'}</td>
                        <td>${column.default !== null ? '<code>' + column.default + '</code>' : '<span class="text-muted">NULL</span>'}</td>
                        <td>${column.key ? '<span class="badge bg-primary">' + column.key + '</span>' : ''}</td>
                    `;
                    tbody.appendChild(row);
                });

                document.getElementById('schemaContent').style.display = 'block';
            })
            .catch(error => {
                console.error('Schema fetch error:', error);
                document.getElementById('schemaLoading').style.display = 'none';
                document.getElementById('schemaError').textContent = '스키마 정보를 가져오는데 실패했습니다: ' + error.message;
                document.getElementById('schemaError').style.display = 'block';
            });
    }

    // 이벤트 위임 사용 (동적 요소에도 작동)
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.show-schema-link');
        if (link) {
            e.preventDefault();
            const tableName = link.dataset.table;
            console.log('Showing schema for table:', tableName);

            // 모달 표시
            const modal = new bootstrap.Modal(document.getElementById('schemaModal'));
            modal.show();

            // 스키마 정보 로드
            showTableSchema(tableName);
        }
    });

    console.log('Schema viewer initialized');

    // 샤딩 토글 이벤트
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('shard-toggle')) {
            const tableId = e.target.dataset.tableId;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

            fetch(`/admin/auth/shards/tables/${tableId}/toggle-sharding`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data.message);
                    // 토글 상태 유지 (이미 변경되어 있음)
                } else {
                    // 실패 시 토글 되돌리기
                    e.target.checked = !e.target.checked;
                    alert('샤딩 설정 변경에 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Toggle error:', error);
                // 오류 시 토글 되돌리기
                e.target.checked = !e.target.checked;
                alert('샤딩 설정 변경 중 오류가 발생했습니다.');
            });
        }
    });
})();
</script>
@endpush
