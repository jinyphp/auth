<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='300'>
            회원
        </th>
        <th>
            만기일자/설명
        </th>

        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width='300'>
                    <x-flex class="gap-2">
                        <x-avata src="/home/user/avatar/{{$item->user_id}}"
                            alt=""
                            class="w-10 h-10 rounded-full"/>
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
                    <x-flex class="gap-2">
                        <x-click wire:click="hook('wireExpire','{{$item->email}}')">
                            만료
                        </x-click>
                        <div>{{$item->expire}}</div>
                        <x-click wire:click="hook('wireRenewal','{{$item->email}}')">
                            연장
                        </x-click>
                    </x-flex>

                    <p>{{$item->description}}</p>

                </td>



                <td width='200'>{{$item->created_at}}</td>
            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
