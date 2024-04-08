<div>
    <div class="row">
        <div class="col-7">
            <h5 class="card-title">
                상위 로그인 사용자
            </h5>
        </div>
        <div class="col-2">
            <x-select-month name="month"
            class="form-select form-select-sm"
            wire:model="month"/>
        </div>
        <div class="col-3">
            <x-select-year name="year"
            class="form-select form-select-sm"
            wire:model="year"/>
        </div>
    </div>


    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>이메일</th>
                <th>이름</th>
                <th>로그횟수</th>
            </tr>
        </thead>
        <tbody>
            @foreach($result as $item)
            <tr>
                <td>{{$item->email}}</td>
                <td>{{$item->name}}</td>
                <td>{{$item->total_cnt}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
