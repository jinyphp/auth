<div>


    <x-datatable>
        <thead>
            <tr>
                <th width='20'>
                    <input type='checkbox' class="form-check-input" wire:model="selectedall">
                </th>
                <th width='50'>Id</th>
                <th >
                    회원
                </th>
                <th >
                    설명
                </th>
                <th width='250'>휴면상태</th>
                <th width='200'>만기일자</th>
                <th width='200'>해제요청</th>
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
                <td width='50'>{{$item->id}}</td>
                <td>
                    <x-flex class="gap-2">
                        <x-avata src="/account/avatas/{{$item->user_id}}"
                            alt=""
                            class="avatar-sm"/>
                        <div>
                            <div>{!! $popupEdit($item, $item->email) !!}</div>
                            <div>{{$item->name}} </div>
                        </div>
                    </x-flex>
                </td>

                <td>
                    {{$item->description}}
                </td>

                <td width='250'>
                    <x-flex class="gap-2">
                        <x-click wire:click="hook('wireSleeper',{{$item->user_id}})">
                            <x-toggle-switch :status="$item->auth"/>
                        </x-click>
                        <div>{{$item->updated_at}}</div>
                    </x-flex>
                </td>

                <td width='200'>
                    {{$item->expire_date}}
                </td>
                <td width='200'>
                    <x-click wire:click="hook('wireUnlock',{{$item->user_id}})">
                        <x-toggle-check :status="$item->unlock"/>
                    </x-click>

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
