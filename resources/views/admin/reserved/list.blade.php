<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='50'>Id</th>
        <th width='150'>이름</th>
        <th>이메일</th>
        <th width='150'>white_ip</th>
        <th width='150'>black_ip</th>
        <th width='200'>등록일자</th>

    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width='50'>
                    {{-- {!! $popupEdit($item, $item->id) !!} --}}
                    <x-link-void wire:click="edit({{$item->id}})">
                        {{$item->id}}
                    </x-link-void>
                </td>
                <td width='150'>
                    {{$item->name}}
                </td>
                <td>
                    {{$item->email}}
                </td>
                <td width='150'>
                    {{$item->white_ip}}
                </td>
                <td width='150'>
                    {{$item->black_ip}}
                </td>
                <td width='200'>{{$item->created_at}}</td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
