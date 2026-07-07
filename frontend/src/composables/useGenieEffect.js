import gsap from 'gsap'
import { nextTick } from 'vue'

export function useGenieEffect() {
  function animateIn(el) {
    if (!el) return
    gsap.killTweensOf(el)

    const tl = gsap.timeline()

    tl
      .set(el, {
        transformOrigin: '50% 100%',
        scaleY: 0,
        scaleX: 0.3,
        y: 80,
        opacity: 0,
      })
      .to(el, {
        scaleY: 1.1,
        scaleX: 1.03,
        y: -6,
        opacity: 1,
        duration: 0.35,
        ease: 'power3.out',
      })
      .to(el, {
        scaleY: 0.96,
        scaleX: 0.99,
        y: 2,
        duration: 0.18,
        ease: 'power2.inOut',
      })
      .to(el, {
        scaleY: 1,
        scaleX: 1,
        y: 0,
        duration: 0.1,
        ease: 'power2.out',
      })
  }

  function onDialogShow() {
    nextTick(() => {
      const dialogs = document.querySelectorAll('.p-dialog[role="dialog"]')
      const el = dialogs[dialogs.length - 1]
      if (el) animateIn(el)
    })
  }

  return { onDialogShow }
}
