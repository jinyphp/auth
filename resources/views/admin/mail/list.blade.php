@php
    $total_users = user_count();
@endphp

<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='200'>사용자</th>
        <th>제목</th>

        <th width='100' class="text-center">발송횟수</th>
        <th width='200'>등록일자</th>

    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td>
                    {{$item->email}}
                </td>
                <td>
                    @if($item->label)
                        <span class="badge bg-secondary">
                            {{$item->label}}
                        </span>
                    @endif
                    <x-link-void wire:click="edit({{$item->id}})">
                        {{$item->subject}}
                    </x-link-void>
                </td>

                <td class="d-none d-xl-table-cell">
                    @if($item->sended == 0)
                        <span class="btn btn-sm btn-primary"
                            wire:click="hook('sendMail', {{ $item->id }})">
                            발송 {{$item->sended}}
                        </span>
                    @else
                        <span class="btn btn-sm btn-success">
                            발송완료 {{$item->sended}}
                        </span>
                    @endif
                </td>

                <td width='200'>{{$item->created_at}}</td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
