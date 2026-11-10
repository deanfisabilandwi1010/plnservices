import gsap from 'gsap';

/**
 * Board member profiles (partials/person-card → partials/person-modal). Opening a card flies
 * its photo into the dialog and unfolds the white sheet out of it; closing sends the photo
 * back to its card. Inside, arrows, dots, ← → keys or a sideways drag on the photo step
 * through the rest of that board.
 */
const pad = (n) => String(n).padStart(2, '0');

export function initPersonModal(reduceMotion) {
    const dialog = document.querySelector('[data-pm]');
    if (!dialog) return;

    const $ = (selector) => dialog.querySelector(selector);
    const panel = $('[data-pm-panel]');
    const sheet = $('[data-pm-sheet]');
    const frame = $('[data-pm-frame]');
    const photo = $('[data-pm-photo]');
    const empty = $('[data-pm-empty]');
    const scrim = $('[data-pm-scrim]');
    const closeButton = $('[data-pm-close]');
    const parts = [...dialog.querySelectorAll('[data-pm-in]')];
    const html = document.documentElement;

    let board = []; // the card buttons of the board being browsed
    let index = 0;
    let flow = null; // the opening or stepping animation; a new action fast-forwards it
    let closing = false;
    const settle = () => flow?.progress(1);

    const read = (trigger) => JSON.parse(trigger.dataset.person);
    const cardPhoto = (trigger) => trigger.closest('article').querySelector('[data-person-photo]');
    const wrap = (i) => (i + board.length) % board.length;
    const show = (trigger, visible) => (cardPhoto(trigger).style.visibility = visible ? '' : 'hidden');

    function fill() {
        const person = read(board[index]);
        $('[data-pm-group]').textContent = board[index].closest('[data-person-group]')?.dataset.personGroup ?? '';
        $('[data-pm-count]').textContent = `${pad(index + 1)} / ${pad(board.length)}`;
        $('[data-pm-name]').textContent = person.name;
        $('[data-pm-position]').textContent = person.position;

        const bio = person.bio.length ? person.bio : [`Profil lengkap ${person.name} akan segera diperbarui.`];
        $('[data-pm-bio]').replaceChildren(...bio.map((text) => Object.assign(document.createElement('p'), { textContent: text })));

        photo.hidden = !person.photo;
        empty.hidden = !!person.photo;
        if (person.photo) {
            photo.src = person.photo;
            photo.alt = person.name;
        } else {
            photo.removeAttribute('src');
            frame.style.background = '';
        }

        const alone = board.length < 2;
        $('[data-pm-nav]').hidden = alone;
        if (!alone) {
            $('[data-pm-prev-name]').textContent = read(board[wrap(index - 1)]).name;
            $('[data-pm-next-name]').textContent = read(board[wrap(index + 1)]).name;
        }

        [...$('[data-pm-dots]').children].forEach((dot, i) => dot.toggleAttribute('aria-current', i === index));
    }

    // Built once per opening, so the dot that has keyboard focus survives stepping.
    function buildDots() {
        $('[data-pm-dots]').replaceChildren(
            ...board.map((trigger, i) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'pm-dot';
                dot.setAttribute('aria-label', `Profil ${read(trigger).name}`);
                dot.addEventListener('click', () => step(i));
                return dot;
            }),
        );
    }

    // The whole photo is shown, so the frame around it takes the colour of the photo's top
    // corners (its studio backdrop) and the two blend into one. A cut-out photo (transparent
    // corners) keeps the frame's default light gradient behind it.
    const backdrop = document.createElement('canvas').getContext('2d', { willReadFrequently: true });
    photo.addEventListener('load', () => {
        try {
            backdrop.canvas.width = backdrop.canvas.height = 16;
            backdrop.clearRect(0, 0, 16, 16);
            backdrop.drawImage(photo, 0, 0, 16, 16);
            const corners = [...backdrop.getImageData(0, 0, 2, 2).data, ...backdrop.getImageData(14, 0, 2, 2).data];
            const channel = (c) => Math.round(corners.filter((_, i) => i % 4 === c).reduce((a, v) => a + v, 0) / 8);
            frame.style.background = channel(3) < 250 ? '' : `rgb(${channel(0)}, ${channel(1)}, ${channel(2)})`; // shorthand: also drops the default gradient
        } catch {
            frame.style.background = ''; // e.g. a photo from another origin: keep the default
        }
    });

    // The photo's place on its card, expressed as a transform of the dialog's photo frame.
    function cardPose(trigger) {
        const from = cardPhoto(trigger).getBoundingClientRect();
        const to = frame.getBoundingClientRect();
        const scale = from.width / to.width;
        return {
            x: from.left - to.left,
            y: from.top - to.top,
            scale,
            clipPath: `inset(0px 0px ${Math.max(0, to.height - from.height / scale)}px 0px round ${10 / scale}px)`,
        };
    }

    // The sheet folded down to just the photo frame.
    function folded() {
        const p = panel.getBoundingClientRect();
        const f = frame.getBoundingClientRect();
        return `inset(${f.top - p.top}px ${p.right - f.right}px ${p.bottom - f.bottom}px ${f.left - p.left}px round 16px)`;
    }

    function open(trigger) {
        if (dialog.open) return;
        board = [...trigger.closest('[data-person-group]').querySelectorAll('[data-person]')];
        index = board.indexOf(trigger);
        buildDots();
        fill();
        dialog.showModal();
        html.classList.add('pm-open');
        window.lenis?.stop();
        if (reduceMotion) return;

        show(trigger, false);
        flow = gsap.timeline()
            .fromTo(scrim, { opacity: 0 }, { opacity: 1, duration: 0.45, ease: 'power1.out' }, 0)
            .fromTo(frame, cardPose(trigger), { x: 0, y: 0, scale: 1, clipPath: 'inset(0px 0px 0px 0px round 0px)', duration: 0.8, ease: 'expo.out', transformOrigin: '0 0', clearProps: 'clipPath' }, 0)
            .fromTo(sheet, { clipPath: folded() }, { clipPath: 'inset(0px 0px 0px 0px round 16px)', duration: 0.75, ease: 'power4.inOut', clearProps: 'clipPath' }, 0.2)
            .fromTo(parts, { opacity: 0, y: 18 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out', stagger: 0.06 }, 0.5)
            .fromTo(closeButton, { opacity: 0, scale: 0.4, rotation: -90 }, { opacity: 1, scale: 1, rotation: 0, duration: 0.5, ease: 'back.out(2)' }, 0.65);
    }

    function close() {
        if (closing || !dialog.open) return;
        if (reduceMotion) return dialog.close();

        settle();
        closing = true;
        const trigger = board[index];
        show(trigger, false);
        gsap.timeline({ onComplete: () => dialog.close() })
            .to([...parts, closeButton], { opacity: 0, y: 10, duration: 0.18, ease: 'power1.in' }, 0)
            // The sheet folds into the photo and fades as it goes, so no white box is left behind
            // once the photo flies home.
            .to(sheet, { clipPath: folded(), opacity: 0, duration: 0.35, ease: 'power2.in' }, 0)
            .to(frame, { ...cardPose(trigger), duration: 0.6, ease: 'expo.inOut', transformOrigin: '0 0' }, 0.1)
            .to(scrim, { opacity: 0, duration: 0.35, ease: 'power1.in' }, 0.3);
    }

    // Leaving the dialog any way at all (Esc included) restores the page.
    dialog.addEventListener('close', () => {
        gsap.killTweensOf([scrim, frame, sheet, photo, closeButton, ...parts]);
        gsap.set([scrim, frame, sheet, photo, closeButton, ...parts], { clearProps: 'all' });
        document.querySelectorAll('[data-person-photo]').forEach((el) => (el.style.visibility = ''));
        html.classList.remove('pm-open');
        window.lenis?.start();
        flow = null;
        closing = false;
    });

    function step(to) {
        const next = wrap(to);
        if (closing || next === index) return;
        const dir = to > index ? 1 : -1; // wrap-around still moves the way the arrow points
        if (reduceMotion) {
            index = next;
            return fill();
        }

        settle(); // a quick second click goes straight on to the next profile
        flow = gsap.timeline()
            .to(photo, { x: -60 * dir, opacity: 0, duration: 0.25, ease: 'power2.in' }, 0)
            .to(parts, { x: -24 * dir, opacity: 0, duration: 0.2, ease: 'power2.in', stagger: 0.02 }, 0)
            .add(() => {
                show(board[index], true);
                index = next;
                show(board[index], false);
                fill();
            })
            .fromTo(photo, { x: 60 * dir, opacity: 0, scale: 1.08 }, { x: 0, opacity: 1, scale: 1, rotation: 0, duration: 0.6, ease: 'power3.out' })
            .fromTo(parts, { x: 24 * dir, opacity: 0 }, { x: 0, opacity: 1, duration: 0.5, ease: 'power3.out', stagger: 0.04 }, '<0.05');
    }

    // Relative steps count from where the running step will land, so quick presses add up.
    const stepBy = (delta) => {
        if (closing) return;
        settle();
        step(index + delta);
    };

    // Drag the photo sideways to move to the next or previous profile.
    let dragStart = null;
    frame.addEventListener('pointerdown', (e) => {
        if (closing || board.length < 2) return;
        settle();
        dragStart = e.clientX;
        frame.setPointerCapture(e.pointerId);
    });
    frame.addEventListener('pointermove', (e) => {
        if (dragStart === null) return;
        const dx = e.clientX - dragStart;
        gsap.set(photo, { x: dx * 0.6, rotation: dx * 0.015 });
    });
    const release = (e) => {
        if (dragStart === null) return;
        const dx = e.clientX - dragStart;
        dragStart = null;
        if (Math.abs(dx) > 70) stepBy(dx < 0 ? 1 : -1);
        else gsap.to(photo, { x: 0, rotation: 0, duration: 0.5, ease: 'elastic.out(1, 0.5)' });
    };
    frame.addEventListener('pointerup', release);
    frame.addEventListener('pointercancel', release);

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-person]');
        if (trigger) open(trigger);
    });
    closeButton.addEventListener('click', close);
    scrim.addEventListener('click', close);
    $('[data-pm-prev]').addEventListener('click', () => stepBy(-1));
    $('[data-pm-next]').addEventListener('click', () => stepBy(1));
    dialog.addEventListener('cancel', (e) => {
        e.preventDefault(); // Esc: play the closing animation first
        close();
    });
    dialog.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight') stepBy(1);
        if (e.key === 'ArrowLeft') stepBy(-1);
    });
}
