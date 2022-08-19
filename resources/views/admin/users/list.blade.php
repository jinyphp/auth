<div>
    {{--
    @if (session()->has('message'))
        <div class="alert alert-success">{{session('message')}}</div>
    @endif
    --}}

    <x-datatable>
        <thead>
            <tr>
                <th width='20'>
                    <input type='checkbox' class="form-check-input" wire:model="selectedall">
                </th>
                <th width="50">Id</th>
                <th width="100">국가</th>

                <th>이름/이메일</th>
                <th width="200">역할</th>
                <th width="200">Varified</th>
                <th width="200">2FA</th>
                <th width="100">승인</th>
                <th width="100">휴면계정</th>
                <th width="200">등록/만료 일자</th>
            </tr>
        </thead>
        <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)

            {{-- row-selected --}}
            @if(in_array($item->id, $selected))
            <tr class="row-selected">
            @else
            <tr>
            @endif

                <td width='20'>
                    <input type='checkbox' name='ids' value="{{$item->id}}"
                    class="form-check-input"
                    wire:model="selected">
                </td>
                <td width="50">{{$item->id}}</td>
                <td width="100">{{$item->country}}</td>

                <td>
                    <div>{{$item->name}}</div>
                    <div>{!! $popupEdit($item, $item->email) !!}</div>
                </td>
                <td width="200">
                    Role
                </td>
                <td width="200">
                    Varified
                </td>
                <td width="200">
                    2FA
                </td>
                <td width="100">
                    {{$item->auth}}
                </td>
                <td width="100">
                    {{$item->sleeper}}
                </td>
                <td width="200">
                    <div class="text-gray-600">{{$item->created_at}} ~</div>
                    <div>{{$item->expire}}</div>
                </td>
            </tr>
            @endforeach
        @endif
        </tbody>
    </x-datatable>

    @if(empty($rows))
    목록이 없습니다.
    @endif
</div>
