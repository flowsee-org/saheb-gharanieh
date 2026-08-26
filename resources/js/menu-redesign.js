const menuRoot = document.getElementById('menu-root');
const chips = Array.from(document.querySelectorAll('.menu-category-link[data-chip]'));
const menuSections = Array.from(document.querySelectorAll('.menu-section[data-section]'));

if (menuRoot && chips.length && menuSections.length) {
    const setActive = (slug, updateUrl = false) => {
        chips.forEach((chip) => {
            chip.setAttribute('aria-current', chip.dataset.chip === slug ? 'true' : 'false');
        });

        const active = chips.find((chip) => chip.dataset.chip === slug);
        active?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });

        if (updateUrl) history.replaceState(null, '', `#${slug}`);
    };

    const scrollTo = (slug) => {
        const target = document.getElementById(slug);
        if (!target) return;
        const nav = document.querySelector('.menu-category-nav');
        const offset = (nav?.getBoundingClientRect().height ?? 0) + 12;
        window.scrollTo({ top: Math.max(0, target.getBoundingClientRect().top + window.scrollY - offset), behavior: 'smooth' });
    };

    chips.forEach((chip) => {
        chip.addEventListener('click', (event) => {
            const slug = chip.dataset.chip;
            if (!slug) return;
            event.preventDefault();
            scrollTo(slug);
            setActive(slug, true);
        });
    });

    let ticking = false;
    const sync = () => {
        const nav = document.querySelector('.menu-category-nav');
        const line = (nav?.getBoundingClientRect().height ?? 0) + 28;
        let current = menuSections[0]?.dataset.section;

        menuSections.forEach((section) => {
            if (section.getBoundingClientRect().top <= line) current = section.dataset.section;
        });

        if (current) setActive(current);
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(sync);
    }, { passive: true });

    sync();
}
