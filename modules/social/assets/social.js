document.addEventListener('DOMContentLoaded', () => {
    const statusFilter = document.querySelector('[data-social-filter="status"]');
    if (!statusFilter) return;

    statusFilter.addEventListener('change', () => {
        const expected = statusFilter.value;
        document.querySelectorAll('.post-card').forEach((card) => {
            const matches = expected === 'all' || card.dataset.status === expected;
            card.style.display = matches ? '' : 'none';
        });
    });
});
