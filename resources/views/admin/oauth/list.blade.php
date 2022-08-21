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
                <th width='200'>provider</th>
                <th>이메일</th>
                <th>oauth_id</th>
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
                <td width='200'>
                    {{$item->provider}}
                </td>
                <td>
                    {!! $popupEdit($item, $item->email) !!}
                </td>
                <td>
                    {{$item->oauth_id}}
                </td>
                <td width='200'>{{$item->created_at}}</td>
            </tr>
            @endforeach
        @else
            목록이 없습니다.
        @endif
        </tbody>
    </x-datatable>

</div>
