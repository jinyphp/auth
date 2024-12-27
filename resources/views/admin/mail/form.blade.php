<div>
    <x-form-hor>
        <x-form-label>이메일</x-form-label>
        <x-form-item>
            {!! xInputText()
                ->setWire('model.defer',"forms.email")
                ->setWidth("standard")
            !!}
        </x-form-item>
    </x-form-hor>

    <x-form-hor>
        <x-form-label>라벨</x-form-label>
        <x-form-item>
            <select class="form-select" wire:model="forms.label">
                <option value="">라벨선택</option>
                @foreach(DB::table('user_mail_label')->where('enable',1)->get() as $label)
                    <option value="{{$label->name}}">{{$label->name}}</option>
                @endforeach
            </select>
        </x-form-item>
    </x-form-hor>

    <x-form-hor>
        <x-form-label>제목</x-form-label>
        <x-form-item>
            {!! xInputText()
                ->setWire('model.defer',"forms.subject")
                ->setWidth("standard")
            !!}
        </x-form-item>
    </x-form-hor>

    <x-form-hor>
        <x-form-label>내용</x-form-label>
        <x-form-item>
            {!! xTextarea()
                ->setWire('model.defer',"forms.message")
            !!}
        </x-form-item>
    </x-form-hor>


    <x-form-hor>
        <x-form-label>즉시발송</x-form-label>
        <x-form-item>
            <input type="checkbox" class="form-check-input"
                wire:model="forms.instant"
                @if(isset($forms['instant']) && $forms['instant']) checked @endif>
        </x-form-item>
    </x-form-hor>




</div>
