@props(['title', 'description', 'action' => null, 'href' => null])
<div class="rounded-2xl border border-dashed border-soft px-6 py-12 text-center">
    <span class="mx-auto grid size-14 place-items-center rounded-full bg-fig-leaf/20 text-2xl text-fresh-grown" aria-hidden="true">⌕</span>
    <h3 class="font-display mt-4 text-xl">{{ $title }}</h3><p class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted">{{ $description }}</p>
    @if($action && $href)<a class="btn-primary mt-5" href="{{ $href }}">{{ $action }}</a>@endif
</div>
