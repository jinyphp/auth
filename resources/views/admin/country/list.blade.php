@php
    $total_users = user_count();
@endphp

<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='100' class="text-center">Id</th>
        <th>국가명</th>
        <th width='100' class="text-center">사용자수</th>
        <th width='100' class="text-center">% Percent</th>
        <th width='200'>등록일자</th>

    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width='100' class="text-center">
                    {{$item->id}}
                </td>
                <td class="d-flex align-items-center gap-2">
                    <img src="/images/flags/{{$item->code}}.png" width="30px">
                    {{-- {!! $popupEdit($item, $item->name) !!} --}}
                    <x-link-void wire:click="edit({{$item->id}})">
                        {{$item->name}}
                    </x-link-void>
                    <span class="badge bg-secondary">
                        {{$item->code}}
                    </span>
                </td>
                <td width='100' class="text-center">
                    {{$item->users}}
                </td>
                <td class="d-none d-xl-table-cell">
                    @php
                        if($item->users > 0 && $total_users > 0) {
                            $percent = $item->users / $total_users * 100;
                            $percent = round($percent, 2);
                        } else {
                            $percent = 0;
                        }

                    @endphp
                    <div class="progress">
                        <div class="progress-bar bg-primary"
                            role="progressbar" style="width: {{ $percent }}%"
                            aria-valuenow="{{ $percent }}" aria-valuemin="0"
                            aria-valuemax="100">{{ $percent }}%</div>
                    </div>
                </td>

                <td width='200'>{{$item->created_at}}</td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
