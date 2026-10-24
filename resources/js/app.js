import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import Lenis from 'lenis';

import { initTransitions, introDelay } from './site/transition';
import { initHeader } from './site/header';
import { initReveals } from './site/reveal';
import { initInteractions } from './site/interactions';
import { initCarousels } from './site/carousel';
import { initUnitStory } from './site/unit-story';
import { initTimelineViz } from './site/timeline-viz';
import { initMaps } from './site/map';

gsap.registerPlugin(ScrollTrigger, SplitText);

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

let lenis = null;
if (!reduceMotion) {
    lenis = new Lenis({ duration: 1.15, smoothWheel: true });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);
    // Pin spacers change the page height; keep Lenis' scroll limit in sync.
    ScrollTrigger.addEventListener('refresh', () => lenis.resize());
}
window.lenis = lenis;

document.addEventListener('DOMContentLoaded', async () => {
    const delay = await introDelay(reduceMotion);

    initTransitions(reduceMotion);
    initHeader(lenis);
    initInteractions();
    initMaps();
    initCarousels();
    initUnitStory(reduceMotion);
    initTimelineViz(reduceMotion);

    if (!reduceMotion) {
        initReveals(delay);
        ScrollTrigger.sort();
        ScrollTrigger.refresh();
    }

    // Fonts can shift layout; recalculate pinned sections once they're in.
    document.fonts?.ready.then(() => ScrollTrigger.refresh());
});
