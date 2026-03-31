<section id="faq" class="py-24 px-8 md:px-16 bg-background">
    <div class="max-w-3xl mx-auto">

        <h2 class="font-serif text-3xl md:text-4xl lg:text-5xl text-foreground text-center mb-16">
            FAQ
        </h2>

        <div class="space-y-0">

            {{-- Item 01 --}}
            <div class="faq-item border-t border-foreground/10 border-b border-b-foreground/10">
                <button
                    class="w-full flex items-center justify-between gap-4 py-5 text-left bg-transparent border-none cursor-pointer"
                    aria-expanded="false"
                    onclick="toggleFaq(this)"
                >
                    <span class="flex items-center gap-4">
                        <span class="text-paragraph text-sm font-light flex-shrink-0">01</span>
                        <span class="text-foreground text-sm md:text-base font-light">What exactly does RenovaSim do?</span>
                    </span>
                    <span class="faq-chevron flex-shrink-0 w-4 h-4 text-paragraph transition-transform duration-300">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><polyline points="6,9 12,15 18,9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
                <div class="faq-content overflow-hidden max-h-0 transition-[max-height] duration-300 ease-in-out">
                    <p class="text-paragraph text-sm font-light leading-relaxed pb-5 pl-10">
                        RenovaSim is an AI-powered tool that helps you estimate renovation costs, generate design simulations, and get detailed material breakdowns — before you hire a single contractor.
                    </p>
                </div>
            </div>

            {{-- Item 02 --}}
            <div class="faq-item border-b border-foreground/10">
                <button
                    class="w-full flex items-center justify-between gap-4 py-5 text-left bg-transparent border-none cursor-pointer"
                    aria-expanded="false"
                    onclick="toggleFaq(this)"
                >
                    <span class="flex items-center gap-4">
                        <span class="text-paragraph text-sm font-light flex-shrink-0">02</span>
                        <span class="text-foreground text-sm md:text-base font-light">How accurate are the cost estimates?</span>
                    </span>
                    <span class="faq-chevron flex-shrink-0 w-4 h-4 text-paragraph transition-transform duration-300">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><polyline points="6,9 12,15 18,9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
                <div class="faq-content overflow-hidden max-h-0 transition-[max-height] duration-300 ease-in-out">
                    <p class="text-paragraph text-sm font-light leading-relaxed pb-5 pl-10">
                        Estimates are based on area size, room type, material selection, and regional pricing data. While not a replacement for a contractor quote, RenovaSim gives you a reliable baseline to plan your budget and avoid being overcharged.
                    </p>
                </div>
            </div>

            {{-- Item 03 --}}
            <div class="faq-item border-b border-foreground/10">
                <button
                    class="w-full flex items-center justify-between gap-4 py-5 text-left bg-transparent border-none cursor-pointer"
                    aria-expanded="false"
                    onclick="toggleFaq(this)"
                >
                    <span class="flex items-center gap-4">
                        <span class="text-paragraph text-sm font-light flex-shrink-0">03</span>
                        <span class="text-foreground text-sm md:text-base font-light">Do I need to upload floor plans or photos?</span>
                    </span>
                    <span class="faq-chevron flex-shrink-0 w-4 h-4 text-paragraph transition-transform duration-300">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><polyline points="6,9 12,15 18,9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
                <div class="faq-content overflow-hidden max-h-0 transition-[max-height] duration-300 ease-in-out">
                    <p class="text-paragraph text-sm font-light leading-relaxed pb-5 pl-10">
                        No. You simply describe your space — room type, dimensions, and renovation scope. RenovaSim handles the rest using AI.
                    </p>
                </div>
            </div>

            {{-- Item 04 --}}
            <div class="faq-item border-b border-foreground/10">
                <button
                    class="w-full flex items-center justify-between gap-4 py-5 text-left bg-transparent border-none cursor-pointer"
                    aria-expanded="false"
                    onclick="toggleFaq(this)"
                >
                    <span class="flex items-center gap-4">
                        <span class="text-paragraph text-sm font-light flex-shrink-0">04</span>
                        <span class="text-foreground text-sm md:text-base font-light">What's the difference between the Free and Smart plans?</span>
                    </span>
                    <span class="faq-chevron flex-shrink-0 w-4 h-4 text-paragraph transition-transform duration-300">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><polyline points="6,9 12,15 18,9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
                <div class="faq-content overflow-hidden max-h-0 transition-[max-height] duration-300 ease-in-out">
                    <p class="text-paragraph text-sm font-light leading-relaxed pb-5 pl-10">
                        The Free plan covers basic cost estimation and a simple material breakdown for 1 project. Smart unlocks advanced AI estimation, design simulation, detailed breakdowns, and up to 5 projects. Pro is built for professionals needing unlimited projects, full AI design, PDF exports, and priority processing.
                    </p>
                </div>
            </div>

            {{-- Item 05 --}}
            <div class="faq-item border-b border-foreground/10">
                <button
                    class="w-full flex items-center justify-between gap-4 py-5 text-left bg-transparent border-none cursor-pointer"
                    aria-expanded="false"
                    onclick="toggleFaq(this)"
                >
                    <span class="flex items-center gap-4">
                        <span class="text-paragraph text-sm font-light flex-shrink-0">05</span>
                        <span class="text-foreground text-sm md:text-base font-light">Is my data saved or shared?</span>
                    </span>
                    <span class="faq-chevron flex-shrink-0 w-4 h-4 text-paragraph transition-transform duration-300">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><polyline points="6,9 12,15 18,9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
                <div class="faq-content overflow-hidden max-h-0 transition-[max-height] duration-300 ease-in-out">
                    <p class="text-paragraph text-sm font-light leading-relaxed pb-5 pl-10">
                        Your project data is stored securely and never shared with third parties. You can delete your data at any time from your account settings.
                    </p>
                </div>
            </div>

            {{-- Item 06 --}}
            <div class="faq-item border-b border-foreground/10">
                <button
                    class="w-full flex items-center justify-between gap-4 py-5 text-left bg-transparent border-none cursor-pointer"
                    aria-expanded="false"
                    onclick="toggleFaq(this)"
                >
                    <span class="flex items-center gap-4">
                        <span class="text-paragraph text-sm font-light flex-shrink-0">06</span>
                        <span class="text-foreground text-sm md:text-base font-light">Can I use RenovaSim before committing to a plan?</span>
                    </span>
                    <span class="faq-chevron flex-shrink-0 w-4 h-4 text-paragraph transition-transform duration-300">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><polyline points="6,9 12,15 18,9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
                <div class="faq-content overflow-hidden max-h-0 transition-[max-height] duration-300 ease-in-out">
                    <p class="text-paragraph text-sm font-light leading-relaxed pb-5 pl-10">
                        Yes. The Free plan requires no credit card and gives you immediate access to core features so you can test the platform before upgrading.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- Footer --}}
<x-footer />

{{-- Accordion — move to app.js if preferred --}}
<script>
function toggleFaq(trigger) {
    var item    = trigger.closest('.faq-item');
    var isOpen  = trigger.getAttribute('aria-expanded') === 'true';

    // Close all
    document.querySelectorAll('.faq-item').forEach(function (el) {
        el.querySelector('button').setAttribute('aria-expanded', 'false');
        el.querySelector('.faq-content').style.maxHeight = null;
        el.querySelector('.faq-chevron').style.transform = '';
    });

    // Open clicked if it was closed
    if (!isOpen) {
        trigger.setAttribute('aria-expanded', 'true');
        var content = item.querySelector('.faq-content');
        content.style.maxHeight = content.scrollHeight + 'px';
        item.querySelector('.faq-chevron').style.transform = 'rotate(180deg)';
    }
}
</script>
