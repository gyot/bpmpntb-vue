import { ref, onMounted, onUnmounted } from 'vue';

const scrollY = ref(0);
let rafId = null;
let ticking = false;

function onScroll() {
    if (!ticking) {
        rafId = requestAnimationFrame(() => {
            scrollY.value = window.scrollY;
            ticking = false;
        });
        ticking = true;
    }
}

let initCount = 0;
const prefersReducedMotion = typeof window !== 'undefined'
    ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
    : false;

export function useParallax() {
    onMounted(() => {
        if (initCount === 0) {
            window.addEventListener('scroll', onScroll, { passive: true });
            scrollY.value = window.scrollY;
        }
        initCount++;
    });

    onUnmounted(() => {
        initCount--;
        if (initCount <= 0) {
            window.removeEventListener('scroll', onScroll);
            if (rafId) cancelAnimationFrame(rafId);
            initCount = 0;
        }
    });

    function parallaxStyle(speed = 0.3) {
        if (prefersReducedMotion) return {};
        return {
            transform: `translate3d(0, ${scrollY.value * speed}px, 0)`,
            willChange: 'transform',
        };
    }

    return { scrollY, parallaxStyle, prefersReducedMotion };
}

export function useReveal() {
    const isVisible = ref(false);
    let observer = null;
    let el = null;

    onMounted(() => {
        if (prefersReducedMotion) { isVisible.value = true; return; }
        el = document.querySelector('[data-reveal]');
        if (!el) return;
        observer = new IntersectionObserver(
            ([entry]) => { if (entry.isIntersecting) { isVisible.value = true; observer.disconnect(); } },
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
        );
        observer.observe(el);
    });

    onUnmounted(() => { if (observer) observer.disconnect(); });

    return { isVisible };
}
