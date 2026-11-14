import { onMounted, type Ref } from "vue";
import { animate, stagger } from "motion";

const EASE_OUT_EXPO: [number, number, number, number] = [0.16, 1, 0.3, 1];

export function prefersReducedMotion(): boolean {
  if (typeof window === "undefined") return true;
  return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
}

/**
 * Animates matching children inside a container once on mount.
 * Respects `prefers-reduced-motion` and avoids scroll-triggered slop.
 */
export function useEnterMotion(
  containerRef: Ref<HTMLElement | null>,
  selector = "[data-enter]",
  options: { y?: number; duration?: number; staggerDelay?: number } = {}
) {
  const { y = 14, duration = 0.48, staggerDelay = 0.055 } = options;

  onMounted(() => {
    const root = containerRef.value;
    if (!root || prefersReducedMotion()) return;

    const targets = root.querySelectorAll<HTMLElement>(selector);
    if (!targets.length) return;

    animate(
      targets,
      { opacity: [0, 1], y: [y, 0] },
      {
        duration,
        delay: stagger(staggerDelay),
        ease: EASE_OUT_EXPO,
      }
    );
  });
}

/**
 * Animates a single element once on mount.
 */
export function useHeroMotion(
  elRef: Ref<HTMLElement | null>,
  options: { y?: number; duration?: number; delay?: number } = {}
) {
  const { y = 16, duration = 0.55, delay = 0 } = options;

  onMounted(() => {
    const el = elRef.value;
    if (!el || prefersReducedMotion()) return;

    animate(
      el,
      { opacity: [0, 1], y: [y, 0] },
      {
        duration,
        delay,
        ease: EASE_OUT_EXPO,
      }
    );
  });
}
