<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th>동의서ID</th>
        <th width='100' class="text-center">동의여부</th>
        <th width='200'>일자</th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                {{-- <td width='250' class="d-flex gap-2">
                    <img src="/home/user/avatar/{{$item->user_id}}" alt="{{$item->name}}"
                    class="rounded-full w-8 h-8">
                    <div class="text-sm">
                        <div class="text-gray-500">{{$item->name}}</div>
                        <div class="text-gray-500">{{$item->email}}</div>
                    </div>
                </td> --}}
                <td>
                    <x-link-void wire:click="edit({{$item->id}})">
                    {{ $item->agree }}
                    </x-link-void>
                </td>
                <td width='100' class="text-center">
                    @if ($item->checked == 1)
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-check-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                                <path
                                    d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                            </svg>
                        @endif
                </td>
                <td width='200'>{{$item->checked_at}}</td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
