<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='50'>Id</th>
        <th>
            회원
        </th>

        <th width='200'>country/ip</th>
        <th width='200'>언어</th>
        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width='50'>
                    {{$item->id}}
                </td>
                <td>
                    <x-flex class="gap-2">
                        <x-avata src="/home/user/avatar/{{$item->user_id}}"
                            alt=""
                            class="rounded-circle w-8 h-8"/>
                        <div>
                            <div>
                                {{-- {!! $popupEdit($item, $item->email) !!} --}}
                                <x-link-void wire:click="edit({{$item->id}})">
                                    {{$item->email}}
                                </x-link-void>
                            </div>

                        </div>
                    </x-flex>
                </td>
                <td width='200'>
                    <div>{{$item->country}}</div>
                    <div>{{$item->ip}}</div>
                </td>
                <td width='200'>
                    {{$item->language}}
                </td>
                <td width='200'>
                    {{$item->created_at}}
                </td>
            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
