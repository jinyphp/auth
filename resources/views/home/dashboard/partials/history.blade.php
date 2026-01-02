<div class="row mb-4" id="login-history-section">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">최근 로그인 기록</h4>
                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" onclick="fetchLoginHistory()"
                    title="새로고침">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z" />
                        <path
                            d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z" />
                    </svg>
                </button>
            </div>
            <div class="card-body p-0">
                <div id="login-history-loading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div id="login-history-content" class="table-responsive" style="display: none;">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>일시</th>
                                <th>IP 주소</th>
                                <th>브라우저</th>
                                <th>상태</th>
                            </tr>
                        </thead>
                        <tbody id="login-history-table-body">
                            <!-- Data will be inserted here -->
                        </tbody>
                    </table>
                </div>

                <div id="login-history-empty" class="text-center py-4" style="display: none;">
                    <p class="text-muted mb-0">로그인 기록이 없습니다.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetchLoginHistory();
    });

    function fetchLoginHistory() {
        const loadingEl = document.getElementById('login-history-loading');
        const contentEl = document.getElementById('login-history-content');
        const emptyEl = document.getElementById('login-history-empty');
        const tbody = document.getElementById('login-history-table-body');

        loadingEl.style.display = 'block';
        contentEl.style.display = 'none';
        emptyEl.style.display = 'none';

        fetch('/api/auth/v1/log/history', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    // Add Authorization header if needed, but session cookies usually suffice for web
                }
            })
            .then(response => response.json())
            .then(data => {
                loadingEl.style.display = 'none';
                if (data.history && Array.isArray(data.history) && data.history.length > 0) {
                    tbody.innerHTML = '';
                    data.history.forEach(item => {
                        const date = new Date(item.attempted_at);
                        const formattedDate = date.getFullYear() + '-' +
                            String(date.getMonth() + 1).padStart(2, '0') + '-' +
                            String(date.getDate()).padStart(2, '0') + ' ' +
                            String(date.getHours()).padStart(2, '0') + ':' +
                            String(date.getMinutes()).padStart(2, '0') + ':' +
                            String(date.getSeconds()).padStart(2, '0');

                        const statusBadge = item.successful ?
                            '<span class="badge bg-success">성공</span>' :
                            '<span class="badge bg-danger">실패</span>';

                        const row = `
                        <tr>
                            <td>${formattedDate}</td>
                            <td>${item.ip_address}</td>
                            <td class="text-truncate" style="max-width: 200px;" title="${item.user_agent || ''}">
                                ${item.user_agent || '-'}
                            </td>
                            <td>${statusBadge}</td>
                        </tr>
                    `;
                        tbody.innerHTML += row;
                    });
                    contentEl.style.display = 'block';
                } else {
                    emptyEl.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error fetching login history:', error);
                loadingEl.style.display = 'none';
                emptyEl.style.display = 'block';
                emptyEl.innerHTML = '<p class="text-danger mb-0">데이터를 불러오는 중 오류가 발생했습니다.</p>';
            });
    }
</script>
