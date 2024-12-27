@php
    $total_users = user_count();
@endphp

<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='50'>Id</th>
        <th >
            등급명
        </th>
        <th width='100'>user</th>
        <th width='100' class="text-center">% Percent</th>
        <th width='200'>등록일자</th>

    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width='50'>{{$item->id}}</td>
                <td >
                    {{-- {!! $popupEdit($item, $item->name) !!} --}}
                    <x-link-void wire:click="edit({{$item->id}})">
                        @if($item->enable == 1)
                        {{$item->name}}
                        @else
                        <span class="text-decoration-line-through">{{$item->name}}</span>
                        @endif
                    </x-link-void>

                    @if($item->description)
                        <div class="text-muted">
                            {{$item->description}}
                        </div>
                    @endif
                </td>
                <td width='100'>{{$item->users}}</td>
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
