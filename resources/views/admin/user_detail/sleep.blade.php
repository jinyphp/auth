<div class="card flex-fill">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <h5 class="card-title mb-0">휴면관리</h5>

            @if ($sleep->sleeper)
                <button class="btn btn-sm btn-danger" wire:click="sleepCancel">휴면해제</button>
            @else
                <button class="btn btn-sm btn-success" wire:click="sleepAccept">휴면전환</button>
            @endif
        </div>

        <h6 class="card-subtitle text-muted">
            일정 기간동안 접속을 하지 않는 경우에는 휴면회원 상태로 전환됩니다.
        </h6>



        <div class="mt-2"> 다음 만료일자: {{ $sleep->expire_date }}</div>
        <div class="mt-2">
            {{ $sleep->description }}
        </div>
    </div>
</div>
