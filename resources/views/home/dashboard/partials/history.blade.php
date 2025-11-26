@if ($recentLogins && count($recentLogins) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">최근 로그인 기록</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>일시</th>
                                            <th>IP 주소</th>
                                            <th>브라우저</th>
                                            <th>상태</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentLogins as $login)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($login->attempted_at)->format('Y-m-d H:i:s') }}
                                                </td>
                                                <td>{{ $login->ip_address }}</td>
                                                <td class="text-truncate" style="max-width: 200px;">
                                                    {{ $login->user_agent ?? '-' }}</td>
                                                <td>
                                                    @if ($login->successful)
                                                        <span class="badge bg-success">성공</span>
                                                    @else
                                                        <span class="badge bg-danger">실패</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
