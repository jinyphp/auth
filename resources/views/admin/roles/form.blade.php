<x-form-hor>
    <x-form-label>이메일</x-form-label>
    <x-form-item>
        {!! xInputText()->setWire('model.defer', 'forms.email')->setWidth('standard') !!}
    </x-form-item>
</x-form-hor>

<div class="mb-3">
    <label for="profile-name" class="form-label">
        <a href="/admin/permit/roles">
            권환 및 역할
        </a>
    </label>
    <select class="form-select" wire:model.defer="forms.role">
        @if (!isset($forms['role']))
            <option value="">역할을 지정해 주세요</option>
        @endif
        @foreach (DB::table('roles')->where('enable', 1)->orderBy('name')->get() as $role)
            <option value="{{ $role->id }}:{{ $role->name }}">
                {{ $role->name }}
            </option>
        @endforeach
    </select>
</div>


<table class="table table-bordered">
    <thead>
        <tr>
            <th class="text-center">목록</th>
            <th class="text-center">상세</th>
            <th class="text-center">쓰기</th>
            <th class="text-center">수정</th>
            <th class="text-center">삭제</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">
                <input type="checkbox" class="form-check-input" wire:model="forms._read"
            {{ isset($forms['_read']) && $forms['_read'] ? 'checked' : '' }}>
            </td>
            <td class="text-center">
                <input type="checkbox" class="form-check-input" wire:model="forms._view"
            {{ isset($forms['_view']) && $forms['_view'] ? 'checked' : '' }}>
            </td>
            <td class="text-center">
                <input type="checkbox" class="form-check-input" wire:model="forms._write"
            {{ isset($forms['_write']) && $forms['_write'] ? 'checked' : '' }}>
            </td>
            <td class="text-center">
                <input type="checkbox" class="form-check-input" wire:model="forms._update"
            {{ isset($forms['_update']) && $forms['_update'] ? 'checked' : '' }}>
            </td>
            <td class="text-center">
                <input type="checkbox" class="form-check-input" wire:model="forms._delete"
            {{ isset($forms['_delete']) && $forms['_delete'] ? 'checked' : '' }}>
            </td>
        </tr>
    </tbody>
</table>
