import { animate, inView, stagger } from "motion";

const EASE_EXPO: [number, number, number, number] = [0.16, 1, 0.3, 1];

const reducedMotion =
  typeof window !== "undefined" &&
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

function animateWithoutCssConflict(
  elements: HTMLElement[],
  keyframes: Record<string, unknown>,
  options: Record<string, unknown>
) {
  if (!elements.length) return;
  for (const el of elements) {
    el.style.transition = "none";
  }
  const controls = animate(elements, keyframes as any, options as any);
  controls.finished
    .then(() => {
      for (const el of elements) {
        el.style.transition = "";
      }
    })
    .catch(() => {
      for (const el of elements) {
        el.style.transition = "";
      }
    });
}

function initLandingMotion() {
  if (reducedMotion) return;

  // 1. Hero entrance (runs once): animate wrapper divs so .btn-gold CSS hover never double-bounces
  const heroTargets = Array.from(
    document.querySelectorAll<HTMLElement>(
      ".hero-layered-overlay .hero-title-graphic, .hero-layered-overlay .hero-tagline-text, .hero-layered-overlay > div"
    )
  );
  animateWithoutCssConflict(
    heroTargets,
    { opacity: [0, 1], y: [20, 0] },
    { duration: 0.58, delay: stagger(0.09), ease: EASE_EXPO }
  );

  // 2. Section stagger reveal (runs strictly once per container)
  const revealOnce = (
    containerSelector: string,
    itemSelector: string,
    options: { y?: number; staggerDelay?: number; bounce?: boolean } = {}
  ) => {
    const { y = 20, staggerDelay = 0.08, bounce = false } = options;
    const container = document.querySelector<HTMLElement>(containerSelector);
    if (!container || container.dataset.motionDone === "1") return;

    const stop = inView(
      container,
      () => {
        if (container.dataset.motionDone === "1") return;
        container.dataset.motionDone = "1";
        stop();

        const items = Array.from(
          container.querySelectorAll<HTMLElement>(itemSelector)
        ).filter((el) => el.offsetParent !== null);

        animateWithoutCssConflict(
          items,
          bounce
            ? { opacity: [0, 1], y: [y, 0], scale: [0.96, 1] }
            : { opacity: [0, 1], y: [y, 0] },
          bounce
            ? {
                type: "spring",
                stiffness: 260,
                damping: 22,
                delay: stagger(staggerDelay),
              }
            : {
                duration: 0.5,
                delay: stagger(staggerDelay),
                ease: EASE_EXPO,
              }
        );
      },
      { amount: 0.12 }
    );
  };

  revealOnce("#tentang", ".max-w-4xl > *", { y: 16, staggerDelay: 0.07 });
  revealOnce("#kandidat", ".candidate-card-wrapper, .blm-card", {
    y: 20,
    staggerDelay: 0.08,
    bounce: true,
  });
  revealOnce("#voting", ".grid > div", { y: 18, staggerDelay: 0.07 });
  revealOnce("#jadwal", ".timeline-scroll-card", {
    y: 20,
    staggerDelay: 0.08,
    bounce: true,
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initLandingMotion);
} else {
  initLandingMotion();
}
