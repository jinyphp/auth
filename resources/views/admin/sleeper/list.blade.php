<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
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

    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width='50'>{{$item->id}}</td>
                <td>
                    <x-flex class="gap-2">
                        <x-avata src="/account/avatas/{{$item->user_id}}"
                            alt=""
                            class="avatar-sm"/>
                        <div>
                            <div>
                                {{-- {!! $popupEdit($item, $item->email) !!} --}}
                                <x-link-void wire:click="edit({{$item->id}})">
                                    {{$item->email}}
                                </x-link-void>
                            </div>
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

                            <x-toggle-switch :status="$item->sleeper"/>
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

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
