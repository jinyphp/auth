<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='200'>
            회원
        </th>
        <th>
            설명
        </th>
        <th width='200'>
            요청일자
        </th>
        <th width='200'>
            해제요청
        </th>
        <th width='200'>
            등록일자
        </th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}

                <td width='200'>

                    <x-link-void wire:click="edit({{$item->id}})">
                        {{$item->email}}
                    </x-link-void>
                </td>

                <td>
                    @if($item->sleeper)
                    휴면회원 <span>{{$item->expire_date}}</span>
                    @else
                    정상회원
                    @endif
                    {{-- <x-flex class="gap-2">
                        <x-click wire:click="hook('wireSleeper',{{$item->user_id}})">
                                {{$item->sleeper}}
                        </x-click>
                        <div>{{$item->updated_at}}</div>
                    </x-flex> --}}

                    @if($item->description)
                        <p>{{$item->description}}</p>
                    @endif
                </td>

                <td>
                    {{$item->unlock_created_at}}
                </td>


                <td width='200'>
                    @if($item->unlock)
                    <x-click wire:click="hook('wireUnlock',{{$item->user_id}})"
                        class="btn btn-sm btn-info">
                        해제승인
                    </x-click>
                    @else
                    <span>
                        {{$item->unlock_confirmed_at}}
                    </span>
                    @endif
                </td>

                <td width='200'>
                    {{$item->created_at}}
                </td>
            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
