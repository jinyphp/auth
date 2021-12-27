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
                <th width="200">Name</th>
                <th>Email</th>
                <th width="200">Roles</th>
                <th width="200">Varified</th>
                <th width="200">2FA</th>
                <th width="200">Expire</th>
                <th width="200">regdate</th>
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
                <td width="200"><a href="{{route('admin.users.list.profile.index',[ $item->id ])}}">{{$item->name}}</a></td>
                <td>
                    {!! $popupEdit($item, $item->email) !!}
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
                <td width="200">
                    Expire
                </td>
                <td width="200">{{$item->created_at}}</td>
            </tr>
            @endforeach
        @endif
        </tbody>
    </x-datatable>

    @if(empty($rows))
    목록이 없습니다.
    @endif
</div>
