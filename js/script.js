document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('click', (event) => {
        const message = element.getAttribute('data-confirm') || 'Are you sure?';

        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-auto-dismiss="true"]').forEach((alert) => {
    window.setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-6px)';
    }, 3600);

    window.setTimeout(() => {
        alert.remove();
    }, 4100);
});
