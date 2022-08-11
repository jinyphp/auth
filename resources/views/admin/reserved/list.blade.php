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
                <th width='50'>Id</th>
                <th width='150'>이름</th>
                <th>이메일</th>
                <th width='150'>white_ip</th>
                <th width='150'>black_ip</th>
                <th width='200'>등록일자</th>
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
                <td width='50'>
                    {!! $popupEdit($item, $item->id) !!}
                </td>
                <td width='150'>
                    {{$item->name}}
                </td>
                <td>
                    {{$item->email}}
                </td>
                <td width='150'>
                    {{$item->white_ip}}
                </td>
                <td width='150'>
                    {{$item->black_ip}}
                </td>
                <td width='200'>{{$item->created_at}}</td>
            </tr>
            @endforeach
        @else
            사업자 목록이 없습니다.
        @endif
        </tbody>
    </x-datatable>

</div>
