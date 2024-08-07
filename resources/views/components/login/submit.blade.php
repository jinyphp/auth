{{-- class="btn btn-lg btn-primary" --}}
<button type="submit" {{$attributes->merge(['class'=>"btn btn-primary"])}}>
    {{$slot}}
</button>
