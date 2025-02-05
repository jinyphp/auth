<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='100'>Id</th>
        <th >
            회원
        </th>
        <th width='300'>
            만료일자
        </th>
        <th width='150'>재설정</th>
        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td>{{$item->id}}</td>
                <td>
                    <x-flex class="gap-2">
                        <x-avata src="/home/user/avatar/{{$item->user_id}}"
                            alt=""
                            class="w-10 h-10 rounded-full"/>
                        <div>
                            <div>
                                {{-- {!! $popupEdit($item, $item->email) !!} --}}
                                <x-link-void wire:click="edit({{$item->id}})">
                                    {{$item->email}}
                                </x-link-void>

                                <span>({{$item->user_id}})</span>
                            </div>
                            <div>{{$item->name}} </div>
                        </div>
                    </x-flex>
                </td>



               <td>
                    <x-flex class=" align-items-center gap-2">
                        <x-click
                        class="btn btn-sm btn-danger"
                        wire:click="hook('wireExpire','{{$item->email}}')">
                            만료
                        </x-click>
                        <div>{{$item->expire}}</div>
                        <x-click
                        class="btn btn-sm btn-primary"
                        wire:click="hook('wireRenewal','{{$item->email}}')">
                            연장
                        </x-click>
                    </x-flex>

                    {{-- <p>{{$item->description}}</p> --}}
                </td>

                <td>
                    <a class="btn btn-sm btn-secondary"
                        href="/admin/auth/user/password/detail/{{$item->user_id}}">
                        패스워드 설정
                    </a>
                </td>

                <td width='200'>{{$item->created_at}}</td>
            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
