console.log("cursor loaded");

document.addEventListener("DOMContentLoaded", () => {

    // Disable on touch devices
    if ('ontouchstart' in window) return;

    const dot = document.querySelector('.cursor-dot');
    const outline = document.querySelector('.cursor-dot-outline');

    if (!dot || !outline) return;

    let _x = 0, _y = 0;
    let endX = window.innerWidth / 2;
    let endY = window.innerHeight / 2;

    document.addEventListener('mousemove', (e) => {
        // ✅ FIX: use clientX/Y (no scroll bug)
        endX = e.clientX;
        endY = e.clientY;

        dot.style.top = endY + 'px';
        dot.style.left = endX + 'px';

        dot.style.opacity = 1;
        outline.style.opacity = 1;
    });

    function animate() {
        // ✅ Smooth interpolation (better feel)
        _x += (endX - _x) * 0.15;
        _y += (endY - _y) * 0.15;

        outline.style.top = _y + 'px';
        outline.style.left = _x + 'px';

        requestAnimationFrame(animate);
    }

    animate();
});