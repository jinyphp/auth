<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='100'>회원ID</th>
        <th>동의서ID</th>
        <th width='100'>동의여부</th>
        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width='100'>
                    {{$item->user_id}}
                </td>
                <td>
                    {{ $item->agree_id }}

                </td>
                <td width='100'>
                    {{-- {!! $popupEdit($item, $item->agree) !!} --}}
                    <x-link-void wire:click="edit({{$item->id}})">
                        {{$item->agree}}
                    </x-link-void>
                </td>
                <td width='200'>{{$item->created_at}}</td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
