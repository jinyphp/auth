<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th>
            이메일
        </th>
        <th>
            역할명
        </th>

        <th width="100" class="text-center">목록</th>
        <th width="100" class="text-center">상세</th>
        <th width="100" class="text-center">쓰기</th>
        <th width="100" class="text-center">수정</th>
        <th width="100" class="text-center">삭제</th>

    </x-wire-thead>
    <tbody>
        @if (!empty($rows))
            @foreach ($rows as $item)
                <x-wire-tbody-item :selected="$selected" :item="$item">
                    {{-- 테이블 리스트 --}}
                    <td>
                        {{-- {!! $popupEdit($item, $item->name) !!} --}}
                        <x-link-void wire:click="edit({{ $item->id }})">
                            {{ $item->email }}
                        </x-link-void>

                        ({{ $item->user_id }})
                    </td>
                    <td>
                        {{-- {!! $popupEdit($item, $item->name) !!} --}}
                        <x-link-void wire:click="edit({{ $item->id }})">
                            {{ $item->role }}
                        </x-link-void>
                    </td>

                    <td width="100" class="text-center">
                        @if ($item->_read)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                          </svg>
                        @endif
                    </td>
                    <td width="100" class="text-center">
                        @if ($item->_view)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                          </svg>
                        @endif
                    </td>
                    <td width="100" class="text-center">
                        @if ($item->_write)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                          </svg>
                        @endif
                    </td>
                    <td width="100" class="text-center">
                        @if ($item->_update)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                          </svg>
                        @endif
                    </td>
                    <td width="100" class="text-center">
                        @if ($item->_delete)
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                          </svg>
                        @endif
                    </td>


                </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
