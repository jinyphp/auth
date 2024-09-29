<div class="card">
    <div class="card-header">
        <x-flex-between>
            <div>
                <h5 class="card-title">
                    <a href="/admin/auth/country">
                        회원국가
                    </a>
                </h5>
                <h6 class="card-subtitle text-muted">
                    가입된 회원 국가 분석입니다.
                </h6>
            </div>
            <div>
                @icon("info-circle.svg")
            </div>
        </x-flex-between>
    </div>
    <div class="card-body">
        <p>
            {{table_count('user_country')}} 지역
        </p>

        @foreach(table_rows('user_country') as $item)
        <x-badge-info>{{$item->name}}</x-badge-info>
        @endforeach
    </div>
</div>
