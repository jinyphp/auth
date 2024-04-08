<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='100'>필수동의</th>
        <th>제목</th>
        <th width='100'>출력순서</th>
        <th width='200'>등록일자</th>

    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width='100'>
                    {{ $item->required }}
                </td>
                <td>
                    {!! $popupEdit($item, $item->title) !!}
                </td>
                <td width='100'>
                    {{ $item->pos }}
                </td>
                <td width='200'>
                    {{ $item->created_at }}
                </td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
iv>
