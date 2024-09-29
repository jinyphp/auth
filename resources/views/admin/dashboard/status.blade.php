<div class="card">
    <div class="card-header border-bottom">
        <x-flex-between>
            <div>
                <h5 class="card-title">회원상태</h5>
                <h6 class="card-subtitle text-muted">
                    회원별 상태를 확인합니다.
                </h6>
            </div>
            <div>
                @icon("info-circle.svg")
            </div>
        </x-flex-between>
    </div>
    <div class="card-body">
        <x-badge-primary>
            <a href="/admin/auth/confirm">
                confirm
            </a>
        </x-badge-primary>

        <x-badge-info>
            <a href="/admin/auth/sleeper">
                휴면회원
            </a>
        </x-badge-info>

        <x-badge-info>
            <a href="/admin/auth/password">
                페스워드만료
            </a>
        </x-badge-info>


    </div>
</div>
