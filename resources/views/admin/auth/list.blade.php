<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='300'>
            회원
        </th>
        <th width='200'>
            승인
        </th>

        <th>
            설명
        </th>

        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}

                <td width='300' class="d-flex gap-2 align-items-center">
                    <img src="/home/user/avatar/{{$item->user_id}}"
                        alt=""
                        class="w-8 h-8 rounded-full">
                    <div>
                        <div>{{$item->name}}</div>
                        <div>
                            <x-link-void wire:click="edit({{$item->id}})">
                                {{$item->email}}
                            </x-link-void>
                        </div>
                    </div>
                </td>

                <td class="text-center">
                    <div class="d-flex gap-2 align-items-center">
                    @if($item->auth)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </svg>
                        <span class="text-xs text-gray-500">
                            {{$item->auth_date}}
                        </span>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                        </svg>
                    @endif
                    </div>
                </td>

                <td>
                    @if($item->description)
                    <p>{{$item->description}}</p>
                    @endif
                </td>

                {{-- <td width='250'>
                    <x-flex class="gap-2">
                        <x-click wire:click="hook('wireAuth',{{$item->user_id}})">
                            <x-toggle-switch :status="$item->auth"/>
                            {{$item->auth}}
                        </x-click>
                        <div>{{$item->updated_at}}</div>
                    </x-flex>
                </td> --}}

                <td width='200'>{{$item->created_at}}</td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
