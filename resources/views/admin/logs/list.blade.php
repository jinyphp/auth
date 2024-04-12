<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th>사용자</th>
        <th width='200'>사용자ID</th>
        <th width='200'>접속방식</th>
        <th width='200'>로그일자</th>

    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td>
                    @php
                        $title = $item->user->name . " (". $item->user->email . ")";
                    @endphp
                    {{ $title }}
                </td>
                <td width='200'>{{$item->user_id}}</td>
                <td width='200'>{{$item->provider}}</td>
                <td width='200'>{{$item->created_at}}</td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>

