import { animate, inView, stagger } from "motion";

const EASE_EXPO: [number, number, number, number] = [0.16, 1, 0.3, 1];

const reducedMotion =
  typeof window !== "undefined" &&
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

function initLandingMotion() {
  if (reducedMotion) return;

  // 1. Hero entrance: Fade-in + Slide-up + Stagger + subtle spring bounce on CTA
  const heroTargets = document.querySelectorAll<HTMLElement>(
    ".hero-layered-overlay .hero-title-graphic, .hero-layered-overlay .hero-tagline-text, .hero-layered-overlay .btn-gold"
  );
  if (heroTargets.length) {
    animate(
      heroTargets,
      { opacity: [0, 1], y: [24, 0], scale: [0.97, 1] },
      { duration: 0.65, delay: stagger(0.1), ease: EASE_EXPO }
    );
  }

  // Bounce micro-interaction on all gold CTA buttons
  document.querySelectorAll<HTMLElement>(".btn-gold").forEach((btn) => {
    btn.addEventListener("mouseenter", () => {
      animate(btn, { y: -2, scale: 1.02 }, { type: "spring", stiffness: 420, damping: 18 });
    });
    btn.addEventListener("mouseleave", () => {
      animate(btn, { y: 0, scale: 1 }, { type: "spring", stiffness: 360, damping: 22 });
    });
    btn.addEventListener("mousedown", () => {
      animate(btn, { scale: 0.97 }, { duration: 0.1 });
    });
    btn.addEventListener("mouseup", () => {
      animate(btn, { scale: 1.02 }, { type: "spring", stiffness: 450, damping: 18 });
    });
  });

  // 2. Helper for section stagger fade-in + slide-up via inView
  const revealStagger = (
    containerSelector: string,
    itemSelector: string,
    options: { y?: number; staggerDelay?: number; bounce?: boolean } = {}
  ) => {
    const { y = 20, staggerDelay = 0.08, bounce = false } = options;
    const container = document.querySelector<HTMLElement>(containerSelector);
    if (!container) return;

    inView(
      container,
      () => {
        const items = container.querySelectorAll<HTMLElement>(itemSelector);
        if (!items.length) return;
        animate(
          items,
          bounce
            ? { opacity: [0, 1], y: [y, 0], scale: [0.95, 1] }
            : { opacity: [0, 1], y: [y, 0] },
          bounce
            ? {
                type: "spring",
                stiffness: 260,
                damping: 20,
                delay: stagger(staggerDelay),
              }
            : {
                duration: 0.55,
                delay: stagger(staggerDelay),
                ease: EASE_EXPO,
              }
        );
      },
      { amount: 0.15 }
    );
  };

  revealStagger("#tentang", "h2, p, .grid > div", { y: 18, staggerDelay: 0.08 });
  revealStagger("#kandidat", ".candidate-card-wrapper, .blm-card", {
    y: 22,
    staggerDelay: 0.08,
    bounce: true,
  });
  revealStagger("#voting", "h2, p, .grid > div", { y: 18, staggerDelay: 0.07 });
  revealStagger("#jadwal", ".timeline-scroll-card", {
    y: 20,
    staggerDelay: 0.09,
    bounce: true,
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initLandingMotion);
} else {
  initLandingMotion();
}
