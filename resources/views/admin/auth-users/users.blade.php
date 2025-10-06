@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '사용자 관리 | Jiny Auth - Admin Dashboard')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            사용자
                            <span class="fs-5">(총 125명)</span>
                        </h1>
                        <!-- Breadcrumb  -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="/admin/auth">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">사용자</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">
                            <i class="fe fe-user-plus me-2"></i>
                            새 사용자 추가
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card Header -->
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="search" class="form-control" placeholder="사용자 검색..." />
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2 justify-content-end">
                                    <select class="form-select w-auto">
                                        <option>모든 역할</option>
                                        <option>관리자</option>
                                        <option>편집자</option>
                                        <option>사용자</option>
                                    </select>
                                    <select class="form-select w-auto">
                                        <option>모든 상태</option>
                                        <option>활성</option>
                                        <option>비활성</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap table-hover table-centered">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" class="form-check-input">
                                    </th>
                                    <th>이름</th>
                                    <th>이메일</th>
                                    <th>역할</th>
                                    <th>상태</th>
                                    <th>가입일</th>
                                    <th>마지막 로그인</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center flex-row gap-2">
                                            <div class="position-relative">
                                                <img src="{{ asset('assets/images/avatar/avatar-1.jpg') }}"
                                                    alt="" class="rounded-circle avatar-md" />
                                                <a href="#" class="position-absolute mt-5 ms-n4">
                                                    <span class="status bg-success"></span>
                                                </a>
                                            </div>
                                            <div>
                                                <h5 class="mb-0">김철수</h5>
                                                <small class="text-muted">@kimcs</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>kim.chulsoo@example.com</td>
                                    <td>
                                        <span class="badge bg-danger">관리자</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">활성</span>
                                    </td>
                                    <td>2024-01-15</td>
                                    <td>방금 전</td>
                                    <td>
                                        <div class="hstack gap-3">
                                            <a href="#" data-bs-toggle="tooltip" data-placement="top" title="편집">
                                                <i class="fe fe-edit-2"></i>
                                            </a>
                                            <a href="#" data-bs-toggle="tooltip" data-placement="top" title="권한">
                                                <i class="fe fe-shield"></i>
                                            </a>
                                            <a href="#" class="text-danger" data-bs-toggle="tooltip" data-placement="top" title="삭제">
                                                <i class="fe fe-trash"></i>
                                            </a>
                                            <span class="dropdown dropstart">
                                                <a class="btn-icon btn btn-ghost btn-sm rounded-circle"
                                                    href="#" role="button" data-bs-toggle="dropdown"
                                                    data-bs-offset="-20,20" aria-expanded="false">
                                                    <i class="fe fe-more-vertical"></i>
                                                </a>
                                                <span class="dropdown-menu">
                                                    <span class="dropdown-header">설정</span>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-eye dropdown-item-icon"></i>
                                                        상세보기
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-mail dropdown-item-icon"></i>
                                                        이메일 보내기
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-lock dropdown-item-icon"></i>
                                                        비밀번호 재설정
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#">
                                                        <i class="fe fe-user-x dropdown-item-icon"></i>
                                                        계정 정지
                                                    </a>
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center flex-row gap-2">
                                            <div class="position-relative">
                                                <img src="{{ asset('assets/images/avatar/avatar-2.jpg') }}"
                                                    alt="" class="rounded-circle avatar-md" />
                                                <a href="#" class="position-absolute mt-5 ms-n4">
                                                    <span class="status bg-success"></span>
                                                </a>
                                            </div>
                                            <div>
                                                <h5 class="mb-0">이영희</h5>
                                                <small class="text-muted">@leeyh</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>lee.younghee@example.com</td>
                                    <td>
                                        <span class="badge bg-primary">편집자</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">활성</span>
                                    </td>
                                    <td>2024-02-20</td>
                                    <td>2시간 전</td>
                                    <td>
                                        <div class="hstack gap-3">
                                            <a href="#" data-bs-toggle="tooltip" data-placement="top" title="편집">
                                                <i class="fe fe-edit-2"></i>
                                            </a>
                                            <a href="#" data-bs-toggle="tooltip" data-placement="top" title="권한">
                                                <i class="fe fe-shield"></i>
                                            </a>
                                            <a href="#" class="text-danger" data-bs-toggle="tooltip" data-placement="top" title="삭제">
                                                <i class="fe fe-trash"></i>
                                            </a>
                                            <span class="dropdown dropstart">
                                                <a class="btn-icon btn btn-ghost btn-sm rounded-circle"
                                                    href="#" role="button" data-bs-toggle="dropdown"
                                                    data-bs-offset="-20,20" aria-expanded="false">
                                                    <i class="fe fe-more-vertical"></i>
                                                </a>
                                                <span class="dropdown-menu">
                                                    <span class="dropdown-header">설정</span>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-eye dropdown-item-icon"></i>
                                                        상세보기
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-mail dropdown-item-icon"></i>
                                                        이메일 보내기
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-lock dropdown-item-icon"></i>
                                                        비밀번호 재설정
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#">
                                                        <i class="fe fe-user-x dropdown-item-icon"></i>
                                                        계정 정지
                                                    </a>
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center flex-row gap-2">
                                            <div class="position-relative">
                                                <img src="{{ asset('assets/images/avatar/avatar-3.jpg') }}"
                                                    alt="" class="rounded-circle avatar-md" />
                                                <a href="#" class="position-absolute mt-5 ms-n4">
                                                    <span class="status bg-secondary"></span>
                                                </a>
                                            </div>
                                            <div>
                                                <h5 class="mb-0">박민수</h5>
                                                <small class="text-muted">@parkms</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>park.minsoo@example.com</td>
                                    <td>
                                        <span class="badge bg-secondary">사용자</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">비활성</span>
                                    </td>
                                    <td>2024-03-10</td>
                                    <td>3일 전</td>
                                    <td>
                                        <div class="hstack gap-3">
                                            <a href="#" data-bs-toggle="tooltip" data-placement="top" title="편집">
                                                <i class="fe fe-edit-2"></i>
                                            </a>
                                            <a href="#" data-bs-toggle="tooltip" data-placement="top" title="권한">
                                                <i class="fe fe-shield"></i>
                                            </a>
                                            <a href="#" class="text-danger" data-bs-toggle="tooltip" data-placement="top" title="삭제">
                                                <i class="fe fe-trash"></i>
                                            </a>
                                            <span class="dropdown dropstart">
                                                <a class="btn-icon btn btn-ghost btn-sm rounded-circle"
                                                    href="#" role="button" data-bs-toggle="dropdown"
                                                    data-bs-offset="-20,20" aria-expanded="false">
                                                    <i class="fe fe-more-vertical"></i>
                                                </a>
                                                <span class="dropdown-menu">
                                                    <span class="dropdown-header">설정</span>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-eye dropdown-item-icon"></i>
                                                        상세보기
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-mail dropdown-item-icon"></i>
                                                        이메일 보내기
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-lock dropdown-item-icon"></i>
                                                        비밀번호 재설정
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-success" href="#">
                                                        <i class="fe fe-user-check dropdown-item-icon"></i>
                                                        계정 활성화
                                                    </a>
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center flex-row gap-2">
                                            <div class="position-relative">
                                                <img src="{{ asset('assets/images/avatar/avatar-4.jpg') }}"
                                                    alt="" class="rounded-circle avatar-md" />
                                                <a href="#" class="position-absolute mt-5 ms-n4">
                                                    <span class="status bg-success"></span>
                                                </a>
                                            </div>
                                            <div>
                                                <h5 class="mb-0">정수연</h5>
                                                <small class="text-muted">@jungsy</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>jung.suyeon@example.com</td>
                                    <td>
                                        <span class="badge bg-primary">편집자</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">활성</span>
                                    </td>
                                    <td>2024-01-25</td>
                                    <td>1시간 전</td>
                                    <td>
                                        <div class="hstack gap-3">
                                            <a href="#" data-bs-toggle="tooltip" data-placement="top" title="편집">
                                                <i class="fe fe-edit-2"></i>
                                            </a>
                                            <a href="#" data-bs-toggle="tooltip" data-placement="top" title="권한">
                                                <i class="fe fe-shield"></i>
                                            </a>
                                            <a href="#" class="text-danger" data-bs-toggle="tooltip" data-placement="top" title="삭제">
                                                <i class="fe fe-trash"></i>
                                            </a>
                                            <span class="dropdown dropstart">
                                                <a class="btn-icon btn btn-ghost btn-sm rounded-circle"
                                                    href="#" role="button" data-bs-toggle="dropdown"
                                                    data-bs-offset="-20,20" aria-expanded="false">
                                                    <i class="fe fe-more-vertical"></i>
                                                </a>
                                                <span class="dropdown-menu">
                                                    <span class="dropdown-header">설정</span>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-eye dropdown-item-icon"></i>
                                                        상세보기
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-mail dropdown-item-icon"></i>
                                                        이메일 보내기
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-lock dropdown-item-icon"></i>
                                                        비밀번호 재설정
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#">
                                                        <i class="fe fe-user-x dropdown-item-icon"></i>
                                                        계정 정지
                                                    </a>
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center flex-row gap-2">
                                            <div class="position-relative">
                                                <img src="{{ asset('assets/images/avatar/avatar-5.jpg') }}"
                                                    alt="" class="rounded-circle avatar-md" />
                                                <a href="#" class="position-absolute mt-5 ms-n4">
                                                    <span class="status bg-success"></span>
                                                </a>
                                            </div>
                                            <div>
                                                <h5 class="mb-0">강지훈</h5>
                                                <small class="text-muted">@kangjh</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>kang.jihoon@example.com</td>
                                    <td>
                                        <span class="badge bg-secondary">사용자</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">활성</span>
                                    </td>
                                    <td>2024-04-05</td>
                                    <td>30분 전</td>
                                    <td>
                                        <div class="hstack gap-3">
                                            <a href="#" data-bs-toggle="tooltip" data-placement="top" title="편집">
                                                <i class="fe fe-edit-2"></i>
                                            </a>
                                            <a href="#" data-bs-toggle="tooltip" data-placement="top" title="권한">
                                                <i class="fe fe-shield"></i>
                                            </a>
                                            <a href="#" class="text-danger" data-bs-toggle="tooltip" data-placement="top" title="삭제">
                                                <i class="fe fe-trash"></i>
                                            </a>
                                            <span class="dropdown dropstart">
                                                <a class="btn-icon btn btn-ghost btn-sm rounded-circle"
                                                    href="#" role="button" data-bs-toggle="dropdown"
                                                    data-bs-offset="-20,20" aria-expanded="false">
                                                    <i class="fe fe-more-vertical"></i>
                                                </a>
                                                <span class="dropdown-menu">
                                                    <span class="dropdown-header">설정</span>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-eye dropdown-item-icon"></i>
                                                        상세보기
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-mail dropdown-item-icon"></i>
                                                        이메일 보내기
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fe fe-lock dropdown-item-icon"></i>
                                                        비밀번호 재설정
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#">
                                                        <i class="fe fe-user-x dropdown-item-icon"></i>
                                                        계정 정지
                                                    </a>
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <span class="text-muted">총 125개 중 1-5개 표시</span>
                            </div>
                            <div class="col-md-6">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-end mb-0">
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#" tabindex="-1">
                                                <i class="fe fe-chevron-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                                        <li class="page-item"><a class="page-link" href="#">...</a></li>
                                        <li class="page-item"><a class="page-link" href="#">25</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">
                                                <i class="fe fe-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/@popperjs/core/dist/umd/popper.min.js') }}"></script>
    <script>
        // Tooltip initialization
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
@endpush