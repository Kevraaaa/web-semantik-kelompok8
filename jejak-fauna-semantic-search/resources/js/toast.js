const toastRegion = document.querySelector('[data-toast-region]');

export function showToast(message, type = 'success') {
    if (!toastRegion) {
        return;
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.setAttribute('role', 'status');
    toast.textContent = message;
    toastRegion.append(toast);
    window.setTimeout(() => toast.remove(), 4200);
}

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-toast]');
    if (trigger) {
        showToast(trigger.dataset.toast || 'Fitur ini akan aktif setelah backend terhubung');
    }
});
