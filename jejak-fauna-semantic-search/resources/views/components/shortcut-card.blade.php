@props(['icon', 'title', 'description', 'href'])
<a href="{{ $href }}" class="group rounded-2xl border border-soft surface p-5 card-hover">
    <div class="flex items-start justify-between"><span class="grid size-11 place-items-center rounded-xl bg-fig-leaf/20 text-xl text-fresh-grown">{{ $icon }}</span><span class="text-lg text-muted transition group-hover:translate-x-1 group-hover:text-fresh-grown" aria-hidden="true">↗</span></div>
    <h3 class="mt-5 font-display text-xl">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-muted">{{ $description }}</p>
</a>
