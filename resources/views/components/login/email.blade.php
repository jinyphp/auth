<div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control form-control-lg"
        type="email"
        name="email"
        placeholder="Enter your email"
        :value="old('email')"
        required
        autofocus>

    {{$slot}}
</div>
