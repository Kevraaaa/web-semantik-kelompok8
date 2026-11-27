@props(['eyebrow' => null, 'title', 'description' => null, 'action' => null, 'href' => null])
<div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
    <div>
        @if($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
        <h2 class="font-display mt-2 text-3xl leading-tight sm:text-4xl">{{ $title }}</h2>
        @if($description)<p class="mt-3 max-w-2xl text-sm leading-6 text-muted">{{ $description }}</p>@endif
    </div>
    @if($action && $href)<a class="btn-secondary shrink-0" href="{{ $href }}">{{ $action }} <span aria-hidden="true">↗</span></a>@endif
</div>
