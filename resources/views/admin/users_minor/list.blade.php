<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='100'>이름</th>
        <th>이메일</th>
        <th width='200'>보호자</th>
        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if (!empty($rows))
            @foreach ($rows as $item)
                <x-wire-tbody-item :selected="$selected" :item="$item">
                    {{-- 테이블 리스트 --}}
                    <td width='100'>
                        {{ $item->name }}
                    </td>

                    <td>
                        <x-link-void wire:click="edit({{ $item->id }})">
                            {{ $item->email }}
                        </x-link-void>
                    </td>

                    <td width='200'>
                        <a href="/admin/auth/users/minor/{{ $item->id }}">
                            보호자
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
