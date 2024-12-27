<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='100'>사용자ID</th>
        <th width='100' class="text-center">이름</th>
        <th>이메일</th>
        <th width='100'>타입</th>
        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if (!empty($rows))
            @foreach ($rows as $item)
                <x-wire-tbody-item :selected="$selected" :item="$item">
                    {{-- 테이블 리스트 --}}
                    <td width='100'>
                        {{ $item->user_id }}
                    </td>
                    <td width='100' class="text-center">
                        {{ $item->name }}
                    </td>
                    <td>
                        {{ $item->email }}
                    </td>
                    <td width='100'>
                        {{ $item->utype }}
                    </td>

                    <td width='200'>
                        {{ $item->created_at }}
                    </td>

                </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
