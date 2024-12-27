<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>

            <div class="mb-3">
                <label for="simpleinput" class="form-label">사용자Id</label>
                <input type="text" class="form-control"
                            wire:model.defer="forms.user_id">
            </div>

            <x-form-hor>
                <x-form-label>활성화</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.enable")
                    !!}
                </x-form-item>
            </x-form-hor>



            <div class="mb-3">
                <label for="simpleinput" class="form-label">이미지</label>
                <input type="file" class="form-control"
                            wire:model.defer="forms.image">
                <p>
                    @if(isset($forms['image']))
                    {{$forms['image']}}
                    @endif
                </p>
            </div>





            <x-form-hor>
                <x-form-label>메모</x-form-label>
                <x-form-item>
                    {!! xTextarea()
                        ->setWire('model.defer',"forms.description")
                    !!}
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>
        <!-- Tab end -->

    </x-navtab>
</div>
