<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>이메일</x-form-label>
                <x-form-item>
                    {{$forms['email']}}
                    {{-- <input type="text" class="form-control"
                        wire:model="forms.email"> --}}

                    {{-- {!! xInputText()
                        ->setWire('model.live',"forms.email")
                        ->setWidth("standard")
                    !!} --}}
                </x-form-item>
            </x-form-hor>


            <x-form-hor>
                <x-form-label>동의서</x-form-label>
                <x-form-item>
                    {{-- {!! xInputText()
                        ->setWire('model.defer',"forms.agree_id")
                        ->setWidth("standard")
                    !!} --}}
                    <select class="form-control" wire:model="forms.agree">
                        <option value="">동의서 선택</option>
                        @foreach (DB::table('user_agreement')->get() as $item)
                            <option value="{{ $item->id }}:{{ $item->title }}">
                                {{ $item->title }}
                            </option>
                        @endforeach
                    </select>
                </x-form-item>
            </x-form-hor>




            <x-form-hor>
                <x-form-label>동의여부</x-form-label>
                <x-form-item>
                    <input type="checkbox" class="form-check-input"
                    wire:model="forms.checked"
                    {{ isset($forms['checked']) && $forms['checked'] == 1 ? 'checked' : '' }}>
                    {{-- {!! xCheckbox()
                        ->setWire('model.defer',"forms.checked")
                    !!} --}}
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>

    </x-navtab>
</div>
