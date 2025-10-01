@extends('jiny-auth::layouts.admin.sidebar')
@section('title', '예약 키워드 수정')
@section('content')
<div class="container-fluid p-6">
    <div class="row"><div class="col-12"><div class="border-bottom pb-3 mb-3"><h1 class="h2 fw-bold">예약 키워드 수정</h1></div></div></div>
    <div class="row"><div class="col-lg-8">
            <div class="card"><div class="card-body">
                    <form action="{{ route('admin.auth.user.reserved.update', $reserved->id) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label for="keyword" class="form-label">키워드 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('keyword') is-invalid @enderror" id="keyword" name="keyword" value="{{ old('keyword', $reserved->keyword) }}" required>
                            @error('keyword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">타입 <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" {{ old('type', $reserved->type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">설명</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $reserved->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.auth.user.reserved.show', $reserved->id) }}" class="btn btn-outline-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">업데이트</button>
                        </div>
                    </form>
                </div></div>
        </div></div>
</div>
@endsection
