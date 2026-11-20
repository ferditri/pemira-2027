import { animate, inView, scroll, stagger } from "motion";

const EASE_EXPO: [number, number, number, number] = [0.16, 1, 0.3, 1];

const reducedMotion =
  typeof window !== "undefined" &&
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

function primeHidden(elements: HTMLElement[], yOffset = 24, scale = 1) {
  for (const el of elements) {
    el.style.opacity = "0";
    el.style.transform =
      scale !== 1
        ? `translateY(${yOffset}px) scale(${scale})`
        : `translateY(${yOffset}px)`;
  }
}

function animateCleanly(
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
        el.style.opacity = "1";
        el.style.transform = "";
        el.style.filter = "";
        el.style.transition = "";
      }
      controls.cancel();
    })
    .catch(() => {
      for (const el of elements) {
        el.style.opacity = "1";
        el.style.transform = "";
        el.style.filter = "";
        el.style.transition = "";
      }
    });
}

function initScrollProgressBar() {
  if (document.getElementById("pemira-scroll-progress")) return;

  const bar = document.createElement("div");
  bar.id = "pemira-scroll-progress";
  bar.setAttribute("aria-hidden", "true");
  Object.assign(bar.style, {
    position: "fixed",
    top: "0",
    left: "0",
    right: "0",
    height: "3px",
    background: "linear-gradient(90deg, #b27318 0%, #f7ca62 50%, #ffe699 100%)",
    boxShadow: "0 0 10px rgba(247, 202, 98, 0.65)",
    transformOrigin: "left center",
    transform: "scaleX(0)",
    zIndex: "60",
    pointerEvents: "none",
  });
  document.body.appendChild(bar);

  scroll(animate(bar, { scaleX: [0, 1] }, { ease: "linear" }));
}

function initHeroMotion() {
  // Navbar gentle drop-in
  const nav = document.getElementById("main-navbar");
  if (nav) {
    animateCleanly(
      [nav],
      { opacity: [0, 1], y: [-12, 0] },
      { duration: 0.5, ease: EASE_EXPO }
    );
  }

  // Hero layered elements entrance
  const heroTitle = document.querySelector<HTMLElement>(
    ".hero-layered-overlay .hero-title-graphic"
  );
  const heroTagline = document.querySelector<HTMLElement>(
    ".hero-layered-overlay .hero-tagline-text"
  );
  const heroActions = document.querySelector<HTMLElement>(
    ".hero-layered-overlay > div"
  );

  if (heroTitle) {
    animateCleanly(
      [heroTitle],
      { opacity: [0, 1], y: [26, 0], scale: [0.92, 1], filter: ["blur(6px)", "blur(0px)"] },
      { duration: 0.72, ease: EASE_EXPO }
    );
  }

  const subItems = [heroTagline, heroActions].filter(
    (el): el is HTMLElement => el !== null
  );
  if (subItems.length) {
    animateCleanly(
      subItems,
      { opacity: [0, 1], y: [22, 0] },
      { duration: 0.6, delay: stagger(0.12, { startDelay: 0.16 }), ease: EASE_EXPO }
    );
  }

  // Subtle scroll-linked parallax on desktop hero artwork
  const heroSection = document.getElementById("hero");
  const heroOverlay = document.querySelector<HTMLElement>(".hero-layered-overlay");
  if (heroSection && heroOverlay && window.innerWidth >= 768) {
    scroll(
      animate(heroOverlay, { y: [0, 64], opacity: [1, 0.25] }, { ease: "linear" }),
      {
        target: heroSection,
        offset: ["start start", "end start"],
      }
    );
  }
}

function observeReveal(
  target: HTMLElement | string,
  selector: string,
  options: {
    y?: number;
    scale?: number;
    staggerDelay?: number;
    spring?: boolean;
    amount?: "some" | "all" | number;
  } = {}
) {
  const {
    y = 28,
    scale = 1,
    staggerDelay = 0.08,
    spring = false,
    amount = 0.08,
  } = options;

  const container =
    typeof target === "string"
      ? document.querySelector<HTMLElement>(target)
      : target;
  if (!container || container.dataset.motionBound === "1") return;
  container.dataset.motionBound = "1";

  const getVisibleItems = () =>
    Array.from(container.querySelectorAll<HTMLElement>(selector)).filter(
      (el) => el.offsetParent !== null
    );

  const initialItems = getVisibleItems();
  if (!initialItems.length) return;

  primeHidden(initialItems, y, scale);

  inView(
    container,
    () => {
      if (container.dataset.motionDone === "1") return;
      container.dataset.motionDone = "1";

      const items = getVisibleItems();
      const keyframes =
        scale !== 1
          ? { opacity: [0, 1], y: [y, 0], scale: [scale, 1] }
          : { opacity: [0, 1], y: [y, 0] };

      const animOptions = spring
        ? {
            type: "spring",
            stiffness: 230,
            damping: 22,
            delay: stagger(staggerDelay),
          }
        : {
            duration: 0.62,
            delay: stagger(staggerDelay),
            ease: EASE_EXPO,
          };

      animateCleanly(items, keyframes, animOptions);
    },
    { amount }
  );
}

function initSectionReveals() {
  // 1. Section: Tentang PEMIRA
  observeReveal(
    "#tentang",
    ".parchment-divider, .section-heading-dark, .space-y-6 > p",
    { y: 26, staggerDelay: 0.09, amount: 0.12 }
  );

  // 2. Section: Kandidat — observe BEM & BLM frames independently so BLM animates when scrolled to
  const candidateBoxes = document.querySelectorAll<HTMLElement>(
    "#kandidat .candidate-frame-box"
  );
  candidateBoxes.forEach((box) => {
    observeReveal(box, ".section-heading-dark, .corner-ornament", {
      y: 20,
      scale: 0.94,
      staggerDelay: 0.05,
      amount: 0.06,
    });
    observeReveal(box, ".blm-card", {
      y: 34,
      scale: 0.93,
      staggerDelay: 0.09,
      spring: true,
      amount: 0.06,
    });
  });

  // 3. Section: Tutorial Voting (fixed selector matching actual DOM)
  observeReveal(
    "#voting",
    ".parchment-divider, p, .video-ornate-container",
    { y: 30, scale: 0.96, staggerDelay: 0.11, spring: true, amount: 0.1 }
  );

  // 4. Section: Jadwal Pelaksanaan (desktop & mobile separately)
  const desktopTimeline = document.querySelector<HTMLElement>(
    "#jadwal .timeline-layered-container"
  );
  if (desktopTimeline) {
    observeReveal(
      desktopTimeline,
      ".timeline-overlay-content > div:first-child, .timeline-badge-circle, .timeline-scroll-card",
      { y: 28, scale: 0.94, staggerDelay: 0.08, spring: true, amount: 0.15 }
    );
  }

  const mobileTimeline = document.querySelector<HTMLElement>(
    "#jadwal > div.md\\:hidden"
  );
  if (mobileTimeline) {
    observeReveal(mobileTimeline, "h2, .timeline-scroll-card", {
      y: 26,
      scale: 0.95,
      staggerDelay: 0.09,
      spring: true,
      amount: 0.08,
    });
  }

  // 5. Footer entrance
  observeReveal(
    "footer.footer-royal",
    "img, .footer-rectangle-panel .flex, .footer-rectangle-panel p",
    { y: 22, staggerDelay: 0.08, amount: 0.1 }
  );
}

function initModalMotion() {
  window.addEventListener("pemira:candidate-modal-open", () => {
    const items = Array.from(
      document.querySelectorAll<HTMLElement>("#modal-body > div")
    );
    if (!items.length) return;

    animateCleanly(
      items,
      { opacity: [0, 1], y: [18, 0], scale: [0.97, 1] },
      { duration: 0.45, delay: stagger(0.07), ease: EASE_EXPO }
    );
  });
}

function initLandingMotion() {
  if (reducedMotion) return;

  initScrollProgressBar();
  initHeroMotion();
  initSectionReveals();
  initModalMotion();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initLandingMotion);
} else {
  initLandingMotion();
}
