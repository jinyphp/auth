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
                <th width='100'>user_id</th>
                <th>팀명</th>
                <th width='100'>personal_team</th>
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
                <td width='100'>
                    {{$item->user_id}}
                </td>
                <td>
                    {!! $popupEdit($item, $item->name) !!}
                </td>
                <td width='100'>
                    {{$item->personal_team}}
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
