<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='200'>라벨명</th>
        <th>컬러</th>

        <th width='100' class="text-center">발송횟수</th>
        <th width='200'>등록일자</th>

    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td>
                    <x-link-void wire:click="edit({{$item->id}})">
                        {{$item->name}}
                    </x-link-void>
                </td>
                <td>
                    {{$item->color}}
                </td>

                <td class="d-none d-xl-table-cell">

                </td>

                <td width='200'>{{$item->created_at}}</td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
