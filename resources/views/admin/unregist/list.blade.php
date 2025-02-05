<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th>사용자</th>
        <th width='200'>프로파일</th>
        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if (!empty($rows))
            @foreach ($rows as $item)
                <x-wire-tbody-item :selected="$selected" :item="$item">
                    {{-- 테이블 리스트 --}}
                    <td class="d-flex align-items-center gap-2">
                        <img src="/home/user/avatar/{{ $item->user_id }}"
                            class="w-8 h-8 rounded-full">
                        <div>
                            <div>{{ $item->name }}</div>
                            <x-link-void wire:click="edit({{ $item->id }})">
                                {{ $item->email }}
                            </x-link-void>
                        </div>
                    </td>

                    <td>
                        <a href="/admin/auth/user/{{ $item->user_id }}/profile">
                        프로파일
                        </a>
                    </td>

                    <td width='200'>
                        {{ $item->created_at }}
                    </td>

                </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
