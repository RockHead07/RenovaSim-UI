import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

if (document.body.classList.contains("landing")) {
    const dot = document.querySelector(".cursor-dot");
    const outline = document.querySelector(".cursor-dot-outline");

    document.addEventListener("mousemove", (e) => {
        if (!dot || !outline) return;

        dot.style.left = e.clientX + "px";
        dot.style.top = e.clientY + "px";

        outline.style.left = e.clientX + "px";
        outline.style.top = e.clientY + "px";

        dot.style.opacity = 1;
        outline.style.opacity = 1;
    });
}