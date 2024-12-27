<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th>사용자</th>
        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if (!empty($rows))
            @foreach ($rows as $item)
                <x-wire-tbody-item :selected="$selected" :item="$item">
                    {{-- 테이블 리스트 --}}
                    <td>
                        {{-- {!! $popupEdit($item, $item->title) !!} --}}
                        <x-link-void wire:click="edit({{ $item->id }})">
                            {{ $item->email }}
                        </x-link-void>
                    </td>


                    <td width='200'>
                        {{ $item->created_at }}
                    </td>

                </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
