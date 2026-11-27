@props(['id', 'title'])
<div class="modal-backdrop hidden" data-modal="{{ $id }}" role="presentation">
    <section class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
        <div class="flex items-start justify-between gap-4"><h2 class="font-display text-2xl" id="{{ $id }}-title">{{ $title }}</h2><button class="rounded-full px-2 text-xl text-muted hover:text-[var(--text)]" type="button" data-modal-close aria-label="Tutup dialog">×</button></div>
        <div class="mt-5">{{ $slot }}</div>
    </section>
</div>
