/**
 * OSTERIA DEL BORGO · Heritage Collection
 * Vanilla JavaScript, progressively enhanced with GSAP, Lenis and Swiper.
 * No API calls: the forms are demonstrations, not real transactions.
 * Personal information is never persisted. Only product IDs / quantities / mode
 * are stored locally so that an interrupted visit does not lose the basket.
 */
(() => {
    'use strict';

    const $ = (selector, scope = document) => scope.querySelector(selector);
    const $$ = (selector, scope = document) => [...scope.querySelectorAll(selector)];
    const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
    const gsap = window.gsap;
    const ScrollTrigger = window.ScrollTrigger;
    const currency = new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' });
    const euros = cents => currency.format(cents / 100);
    const catalog = JSON.parse($('#catalog-data').textContent);
    const photos = JSON.parse($('#gallery-data').textContent);
    const STORE_KEY = 'osteria-del-borgo:cart:v1';
    const MAX_QUANTITY = 20;
    const MAX_DAYS = 90;
    const DURATION = () => motionQuery.matches ? 0 : 0.65;
    let lenis;
    let gallerySwiper;
    let reviewsSwiper;
    let refreshTimer;
    let toastTimer;
    let cart = new Map();
    let mode = 'delivery';
    let isCheckout = false;
    let lightboxIndex = 0;
    const dialogOpeners = new WeakMap();

    const icon = name => `<svg class="icon" aria-hidden="true" focusable="false"><use href="#i-${name}"></use></svg>`;
    const escapeHTML = value => String(value).replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));

    /** Small wrapper keeps all interactions functional if a motion library fails. */
    function animate(target, properties) {
        if (!target) { properties.onComplete?.(); return; }
        if (gsap && !motionQuery.matches) {
            return gsap.to(target, { duration: DURATION(), ease: 'power2.out', overwrite: 'auto', ...properties });
        }
        if (gsap) {
            const { onComplete, duration, delay, ease, ...styles } = properties;
            gsap.set(target, styles);
            onComplete?.();
        } else {
            const { onComplete } = properties;
            const elements = typeof target === 'string' ? $$(target) : target instanceof Element ? [target] : [...target];
            elements.forEach(element => {
                if (properties.autoAlpha !== undefined) {
                    element.style.opacity = properties.autoAlpha;
                    element.style.visibility = properties.autoAlpha ? 'visible' : 'hidden';
                }
                if (properties.height !== undefined) element.style.height = typeof properties.height === 'number' ? `${properties.height}px` : properties.height;
                if (properties.clearProps) properties.clearProps.split(',').forEach(key => element.style.removeProperty(key.trim()));
            });
            onComplete?.();
        }
    }

    function refreshScroll() {
        clearTimeout(refreshTimer);
        refreshTimer = setTimeout(() => {
            lenis?.resize();
            ScrollTrigger?.refresh();
        }, 120);
    }

    function scrollTo(target, immediate = false) {
        const element = typeof target === 'string' ? $(target) : target;
        if (!element) return;
        const offset = -($('#site-header').offsetHeight + 16);
        if (lenis) {
            lenis.scrollTo(element, { offset, duration: immediate ? 0 : 1.3, immediate });
        } else {
            const top = element.getBoundingClientRect().top + window.scrollY + offset;
            window.scrollTo({ top: Math.max(0, top), behavior: immediate || motionQuery.matches ? 'instant' : 'smooth' });
        }
    }

    /* ── Dialogs: a single shared lifecycle, native focus trap and Escape. ── */
    function unlockPage() {
        if (!$('dialog[open]')) {
            document.body.classList.remove('has-dialog');
            lenis?.start();
        }
    }

    function openDialog(dialog, returnFocus = document.activeElement) {
        // A success dialog may replace the checkout without stacking focus traps.
        $$('dialog[open]').forEach(other => {
            gsap?.killTweensOf(other);
            other.close();
        });
        dialogOpeners.set(dialog, returnFocus);
        lenis?.stop();
        document.body.classList.add('has-dialog');
        dialog.showModal();
        dialog.scrollTop = 0;
        if (gsap && !motionQuery.matches) {
            if (dialog.classList.contains('cart-drawer') || dialog.classList.contains('nav-dialog')) {
                gsap.fromTo(dialog, { xPercent: 100, opacity: 1 }, { xPercent: 0, duration: 0.65, ease: 'power3.out', clearProps: 'transform' });
            } else {
                gsap.fromTo(dialog, { opacity: 0, y: 15, scale: 0.98 }, { opacity: 1, y: 0, scale: 1, duration: 0.55, ease: 'power2.out', clearProps: 'transform,opacity' });
            }
        }
    }

    function closeDialog(dialog, callback) {
        if (!dialog.open || dialog.dataset.closing === 'true') return;
        dialog.dataset.closing = 'true';
        const finish = () => {
            dialog.close();
            delete dialog.dataset.closing;
            gsap?.set(dialog, { clearProps: 'transform,opacity' });
            unlockPage();
            const opener = dialogOpeners.get(dialog);
            if (opener?.isConnected && !opener.closest('dialog:not([open])')) opener.focus({ preventScroll: true });
            callback?.();
        };
        const isDrawer = dialog.classList.contains('cart-drawer') || dialog.classList.contains('nav-dialog');
        animate(dialog, { ...(isDrawer ? { xPercent: 100 } : { opacity: 0, y: 10 }), duration: 0.35, ease: 'power2.in', onComplete: finish });
    }

    function initDialogs() {
        $$('dialog').forEach(dialog => {
            // Prevent Lenis from intercepting scroll inside native modals.
            dialog.setAttribute('data-lenis-prevent', '');
            dialog.addEventListener('cancel', event => {
                event.preventDefault();
                closeDialog(dialog);
            });
            dialog.addEventListener('close', () => {
                unlockPage();
                if (dialog.id === 'nav-dialog') $('[data-nav-open]').setAttribute('aria-expanded', 'false');
            });
            dialog.addEventListener('click', event => {
                if (event.target !== dialog) return;
                const rect = dialog.getBoundingClientRect();
                const outside = event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom;
                if (outside || dialog.id === 'lightbox') closeDialog(dialog);
            });
        });
        $('[data-nav-open]').addEventListener('click', event => {
            event.currentTarget.setAttribute('aria-expanded', 'true');
            openDialog($('#nav-dialog'));
        });
        $('[data-nav-close]').addEventListener('click', () => closeDialog($('#nav-dialog')));
        $('[data-cart-close]').addEventListener('click', () => closeDialog($('#cart-drawer')));
        $('[data-lightbox-close]').addEventListener('click', () => closeDialog($('#lightbox')));
        $$('[data-success-close]').forEach(button => button.addEventListener('click', () => closeDialog($('#success-dialog'))));
        $$('[data-legal-close]').forEach(button => button.addEventListener('click', () => closeDialog($('#legal-dialog'))));
    }

    /* ── Navigation and scroll state. ── */
    function initNavigation() {
        document.addEventListener('click', event => {
            const link = event.target.closest('a[href^="#"]');
            if (!link || link.getAttribute('href') === '#') return;
            const target = $(link.getAttribute('href'));
            if (!target) return;
            event.preventDefault();
            const navigate = () => {
                scrollTo(target);
                history.replaceState(null, '', link.getAttribute('href'));
                target.setAttribute('tabindex', '-1');
                target.focus({ preventScroll: true });
            };
            const open = $('dialog[open]');
            if (open) closeDialog(open, navigate);
            else navigate();
        });
        $('.back-to-top').addEventListener('click', () => scrollTo('#home'));

        let frame = false;
        let lastTopState = false;
        const updateHeader = () => {
            $('#site-header').classList.toggle('is-scrolled', window.scrollY > 45);
            const topState = window.scrollY > 750;
            if (lastTopState !== topState) {
                animate($('.back-to-top'), { autoAlpha: topState ? 1 : 0, y: topState ? 0 : 10, duration: 0.35 });
                lastTopState = topState;
            }
            frame = false;
        };
        window.addEventListener('scroll', () => {
            if (!frame) {
                requestAnimationFrame(updateHeader);
                frame = true;
            }
        }, { passive: true });
        updateHeader();

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                $$('.nav-link').forEach(link => {
                    const active = link.getAttribute('href') === `#${entry.target.id}`;
                    link.classList.toggle('is-active', active);
                    if (active) link.setAttribute('aria-current', 'location');
                    else link.removeAttribute('aria-current');
                });
            });
        }, { rootMargin: '-20% 0px -65% 0px', threshold: 0 });
        ['home', 'storia', 'menu', 'galleria', 'prenotazione', 'consegna', 'contatti'].forEach(id => observer.observe($(`#${id}`)));
    }

    /* ── Menu tabs: pointer, touch and WAI-ARIA keyboard navigation. ── */
    function setMenuTab(key, focus = false) {
        const activeTab = $(`[data-menu-tab="${key}"]`);
        if (!activeTab) return;
        $$('.menu-tab').forEach(tab => {
            const selected = tab === activeTab;
            tab.classList.toggle('is-active', selected);
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
        });
        $$('.menu-panel').forEach(panel => {
            panel.hidden = panel.id !== `panel-${key}`;
        });
        const panel = $(`#panel-${key}`);
        if (gsap && !motionQuery.matches) {
            gsap.fromTo($$('.dish-card', panel), { y: 18, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: 0.55, stagger: 0.09, ease: 'power2.out', clearProps: 'transform,opacity,visibility' });
        }
        if (focus) activeTab.focus({ preventScroll: true });
        // Horizontal scroll only, without dragging the whole page to the tab.
        const tabs = $('.menu-tabs');
        const left = activeTab.offsetLeft - tabs.clientWidth / 2 + activeTab.offsetWidth / 2;
        tabs.scrollTo({ left, behavior: motionQuery.matches ? 'instant' : 'smooth' });
        refreshScroll();
    }

    function initMenu() {
        const tabs = $$('.menu-tab');
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => setMenuTab(tab.dataset.menuTab));
            tab.addEventListener('keydown', event => {
                let next;
                if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
                if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
                if (event.key === 'Home') next = 0;
                if (event.key === 'End') next = tabs.length - 1;
                if (next === undefined) return;
                event.preventDefault();
                setMenuTab(tabs[next].dataset.menuTab, true);
            });
        });
        $$('[data-menu-target]').forEach(button => button.addEventListener('click', () => {
            const navigate = () => {
                setMenuTab(button.dataset.menuTarget);
                scrollTo('#menu');
                history.replaceState(null, '', '#menu');
            };
            const dialog = $('dialog[open]');
            if (dialog) closeDialog(dialog, navigate);
            else navigate();
        }));
    }

    /* ── Carousels and full-screen photo viewer. ── */
    function initCarousels() {
        if (!window.Swiper) return;
        const speed = motionQuery.matches ? 0 : 850;
        gallerySwiper = new Swiper('.gallery-swiper', {
            slidesPerView: 'auto',
            spaceBetween: 20,
            speed,
            grabCursor: true,
            watchOverflow: true,
            navigation: { prevEl: '.gallery-prev', nextEl: '.gallery-next' },
            breakpoints: { 681: { spaceBetween: 26 }, 1025: { spaceBetween: 32 } },
            a11y: {
                prevSlideMessage: 'Foto precedente', nextSlideMessage: 'Foto successiva',
                firstSlideMessage: 'Questa è la prima foto', lastSlideMessage: 'Questa è l’ultima foto',
                slideLabelMessage: '{{index}} di {{slidesLength}}', containerMessage: 'Fotografie della nostra osteria',
            },
            on: {
                slideChange(swiper) {
                    $('.gallery-fraction').textContent = `${String(swiper.activeIndex + 1).padStart(2, '0')} / ${String(photos.length).padStart(2, '0')}`;
                },
            },
        });
        reviewsSwiper = new Swiper('.reviews-swiper', {
            slidesPerView: 1,
            spaceBetween: 22,
            speed,
            grabCursor: true,
            rewind: true,
            navigation: { prevEl: '.reviews-prev', nextEl: '.reviews-next' },
            pagination: { el: '.reviews-pagination', clickable: true },
            breakpoints: { 681: { slidesPerView: 2 }, 1100: { slidesPerView: 3, spaceBetween: 26 } },
            a11y: {
                prevSlideMessage: 'Recensione precedente', nextSlideMessage: 'Recensione successiva',
                firstSlideMessage: 'Prima recensione', lastSlideMessage: 'Ultima recensione',
                paginationBulletMessage: 'Vai alla recensione {{index}}', slideLabelMessage: '{{index}} di {{slidesLength}}',
                containerMessage: 'Recensioni illustrative dei nostri ospiti',
            },
        });
    }

    function showPhoto(index, fade = true) {
        lightboxIndex = (index + photos.length) % photos.length;
        const photo = photos[lightboxIndex];
        const image = $('#lightbox-image');
        $('.lightbox-zoom').setAttribute('aria-pressed', 'false');
        gsap?.set(image, { scale: 1 });
        const update = () => {
            image.src = `assets/images/${photo.image}.webp`;
            image.alt = `${photo.title}. ${photo.caption}`;
            $('#lightbox-title').textContent = photo.title;
            $('#lightbox-caption').textContent = photo.caption;
            $('.lightbox-counter').textContent = `${String(lightboxIndex + 1).padStart(2, '0')} / ${String(photos.length).padStart(2, '0')}`;
            if (gsap && !motionQuery.matches) gsap.fromTo(image, { opacity: 0 }, { opacity: 1, duration: 0.45 });
        };
        if (fade) animate(image, { opacity: 0, duration: 0.18, onComplete: update });
        else update();
    }

    function initLightbox() {
        $$('[data-lightbox]').forEach(button => button.addEventListener('click', () => {
            if (gallerySwiper && !gallerySwiper.allowClick) return;
            showPhoto(Number(button.dataset.lightbox), false);
            openDialog($('#lightbox'));
        }));
        $('.lightbox-prev').addEventListener('click', () => showPhoto(lightboxIndex - 1));
        $('.lightbox-next').addEventListener('click', () => showPhoto(lightboxIndex + 1));
        $('#lightbox').addEventListener('keydown', event => {
            if (event.key === 'ArrowRight') { event.preventDefault(); showPhoto(lightboxIndex + 1); }
            if (event.key === 'ArrowLeft') { event.preventDefault(); showPhoto(lightboxIndex - 1); }
        });
        $('.lightbox-zoom').addEventListener('click', event => {
            const button = event.currentTarget;
            const zoom = button.getAttribute('aria-pressed') !== 'true';
            button.setAttribute('aria-pressed', String(zoom));
            if (gsap) animate($('#lightbox-image'), { scale: zoom ? 1.65 : 1, duration: 0.7 });
            else $('#lightbox-image').style.transform = zoom ? 'scale(1.65)' : 'none';
        });
    }

    /* ── Shared form validation, with inline errors linked to each input. ── */
    function errorElement(input) {
        return input.closest('.field')?.querySelector('.field-error') || input.form.querySelector(`[data-error-for="${input.name}"]`);
    }

    function setFieldError(input, message) {
        input.setAttribute('aria-invalid', String(Boolean(message)));
        const error = errorElement(input);
        if (error) {
            if (!error.id) error.id = `${input.id || `${input.form.id}-${input.name}`}-error`;
            input.setAttribute('aria-describedby', error.id);
            error.textContent = message;
        }
    }

    function validateForm(form) {
        let firstInvalid;
        $$('input[required], select[required], textarea[required]', form).forEach(input => {
            if (input.disabled) return;
            let error = '';
            const value = input.value.trim();
            if (input.type === 'checkbox') {
                if (!input.checked) error = 'Il consenso è necessario per proseguire.';
            } else if (!value) {
                error = input.tagName === 'SELECT' ? 'Scegliete un’opzione.' : 'Compilate questo campo.';
            } else if (input.type === 'email' && input.validity.typeMismatch) {
                error = 'Inserite un indirizzo email valido.';
            } else if (input.type === 'tel' && (!/^[+\d\s().-]+$/.test(value) || value.replace(/\D/g, '').length < 7 || value.replace(/\D/g, '').length > 15)) {
                error = 'Inserite un numero di telefono valido.';
            } else if (input.minLength > 0 && value.length < input.minLength) {
                error = `Inserite almeno ${input.minLength} caratteri.`;
            } else if (input.type === 'date' && (value < input.min || value > input.max)) {
                error = 'Scegliete una data nei prossimi 90 giorni.';
            }
            setFieldError(input, error);
            if (error && !firstInvalid) firstInvalid = input;
        });
        if (firstInvalid) firstInvalid.focus();
        return !firstInvalid;
    }

    function initFieldFeedback() {
        $$('.form input,.form textarea,.form select').forEach(input => {
            ['input', 'change'].forEach(eventName => input.addEventListener(eventName, () => {
                if (input.getAttribute('aria-invalid') === 'true') setFieldError(input, '');
            }));
        });
    }

    /** Always evaluate restaurant dates in Parma, not in the visitor's timezone. */
    function romeNow() {
        const parts = new Intl.DateTimeFormat('en-GB', {
            timeZone: 'Europe/Rome', year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
        }).formatToParts(new Date());
        const values = Object.fromEntries(parts.map(part => [part.type, part.value]));
        return { date: `${values.year}-${values.month}-${values.day}`, minutes: Number(values.hour) * 60 + Number(values.minute) };
    }

    function updateBookingTimes() {
        const date = $('#bk-date').value;
        const now = romeNow();
        const select = $('#bk-time');
        $$('option', select).forEach(option => {
            if (!option.value) return;
            const [hour, minute] = option.value.split(':').map(Number);
            option.disabled = date === now.date && hour * 60 + minute < now.minutes + 30;
        });
        if (select.selectedOptions[0]?.disabled) select.value = '';
    }

    function showSuccess({ eyebrow, title, message, summary, returnFocus }) {
        $('#success-eyebrow').textContent = eyebrow;
        $('#success-title').textContent = title;
        $('#success-message').textContent = message;
        const box = $('#success-summary');
        box.replaceChildren();
        summary.forEach((line, index) => {
            const element = document.createElement(index === 0 ? 'strong' : 'div');
            element.textContent = line;
            box.append(element);
        });
        openDialog($('#success-dialog'), returnFocus);
        const confetti = $('#confetti');
        gsap?.killTweensOf($$('span', confetti));
        confetti.replaceChildren();
        if (!gsap || motionQuery.matches) return;
        const colors = ['#c8a66a', '#a77946', '#55624b', '#742e2e'];
        for (let index = 0; index < 24; index++) {
            const fleck = document.createElement('span');
            fleck.style.left = `${15 + Math.random() * 70}%`;
            fleck.style.top = '0';
            fleck.style.background = colors[index % colors.length];
            confetti.append(fleck);
            gsap.fromTo(fleck, { y: -20, x: 0, opacity: 0.7, rotation: Math.random() * 180 }, {
                y: 400 + Math.random() * 180, x: (Math.random() - 0.5) * 140,
                rotation: Math.random() * 500, opacity: 0,
                duration: 2.3 + Math.random() * 1.8, delay: Math.random() * 0.35, ease: 'power1.out',
            });
        }
        gsap.fromTo($('.success-icon'), { scale: 0.65 }, { scale: 1, duration: 0.7, ease: 'back.out(1.4)' });
    }

    function initBooking() {
        const form = $('#booking-form');
        const dateInput = $('#bk-date');
        const today = romeNow().date;
        const max = new Date(`${today}T12:00:00Z`);
        max.setUTCDate(max.getUTCDate() + MAX_DAYS);
        dateInput.min = today;
        dateInput.max = max.toISOString().slice(0, 10);
        $('#bk-guests').value = '2';
        dateInput.addEventListener('change', updateBookingTimes);
        form.addEventListener('submit', event => {
            event.preventDefault();
            const valid = validateForm(form);
            let dateError = '';
            if (dateInput.value) {
                if (new Date(`${dateInput.value}T12:00:00Z`).getUTCDay() === 1) dateError = 'Il lunedì ci riposiamo. Scegliete un altro giorno.';
                if (dateInput.value < romeNow().date) dateError = 'Questa data è già trascorsa.';
                if (dateError) setFieldError(dateInput, dateError);
            }
            updateBookingTimes();
            if (!$('#bk-time').value && dateInput.value) setFieldError($('#bk-time'), 'Scegliete un orario ancora disponibile.');
            if (!valid || dateError || !$('#bk-time').value) {
                $('[aria-invalid="true"]', form)?.focus();
                return;
            }
            const data = new FormData(form);
            const firstName = String(data.get('name')).trim().split(/\s+/)[0];
            const date = new Intl.DateTimeFormat('it-IT', { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' }).format(new Date(`${data.get('date')}T12:00:00Z`));
            const button = $('[type="submit"]', form);
            button.disabled = true;
            const original = button.innerHTML;
            button.textContent = 'Prepariamo il riepilogo…';
            window.setTimeout(() => {
                showSuccess({
                    eyebrow: 'Un tavolo, una bella serata',
                    title: 'La tua richiesta è stata inviata.',
                    message: `Grazie, ${firstName}. Ecco il riepilogo della vostra richiesta dimostrativa. Vi aspetteremmo con un sorriso.`,
                    summary: [`${data.get('guests')} ${data.get('guests') === '1' ? 'persona' : 'persone'} · ${data.get('room')}`, `${date} · ore ${data.get('time')}`, 'Conferma simulata · nessuna email inviata'],
                    returnFocus: button,
                });
                form.reset();
                $('#bk-guests').value = '2';
                updateBookingTimes();
                button.innerHTML = original;
                button.disabled = false;
            }, motionQuery.matches ? 0 : 550);
        });
    }

    /* ── Basket state and integer-cent arithmetic. ── */
    function loadCart() {
        try {
            const saved = JSON.parse(localStorage.getItem(STORE_KEY));
            if (!saved || saved.version !== 1 || !Array.isArray(saved.items)) return;
            mode = saved.mode === 'pickup' ? 'pickup' : 'delivery';
            cart = new Map();
            saved.items.forEach(item => {
                if (Object.hasOwn(catalog, item.id) && Number.isInteger(item.quantity) && item.quantity > 0) {
                    cart.set(item.id, Math.min(MAX_QUANTITY, item.quantity));
                }
            });
        } catch (_) {
            // Storage can be blocked by privacy settings. The basket still works.
        }
    }

    function persistCart() {
        try {
            localStorage.setItem(STORE_KEY, JSON.stringify({
                version: 1, mode,
                items: [...cart].map(([id, quantity]) => ({ id, quantity })),
            }));
        } catch (_) { /* An in-memory basket is sufficient for this demo. */ }
    }

    function totals() {
        let quantity = 0;
        let subtotal = 0;
        cart.forEach((count, id) => {
            quantity += count;
            subtotal += Math.round(catalog[id].price * 100) * count;
        });
        const shipping = mode === 'delivery' && quantity && subtotal < 5000 ? 400 : 0;
        return { quantity, subtotal, shipping, total: subtotal + shipping };
    }

    function notify(message, withCart = false) {
        clearTimeout(toastTimer);
        const region = $('#toast-region');
        region.innerHTML = `<div class="toast">${icon('check')}<span>${escapeHTML(message)}</span>${withCart ? '<button type="button" data-toast-cart>Vedi ordine</button>' : ''}</div>`;
        $('[data-toast-cart]', region)?.addEventListener('click', () => {
            region.replaceChildren();
            openCart();
        });
        if (gsap && !motionQuery.matches) gsap.fromTo(region, { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.4 });
        toastTimer = setTimeout(() => animate(region, { opacity: 0, y: 8, duration: 0.35, onComplete: () => region.replaceChildren() }), 4500);
    }

    function renderCart(focusTarget) {
        const { quantity, subtotal, shipping, total } = totals();
        $$('[data-cart-count]').forEach(badge => { badge.textContent = quantity; badge.hidden = quantity === 0; });
        $$('[data-cart-count-inline]').forEach(badge => { badge.textContent = `(${quantity})`; });
        $('.cart-toggle').setAttribute('aria-label', quantity ? `Apri il carrello, ${quantity} piatti` : 'Apri il carrello');
        $('#cart-empty').hidden = quantity !== 0 || isCheckout;
        $('#cart-summary').hidden = quantity === 0;
        $('#checkout-button').disabled = quantity === 0;
        const items = $('#cart-items');
        items.innerHTML = [...cart].map(([id, count]) => {
            const dish = catalog[id];
            return `<li class="cart-item" data-item="${id}">
                <img src="assets/images/${dish.image}.webp" alt="" width="76" height="85">
                <div><h3>${escapeHTML(dish.name)}</h3><span class="cart-item-unit">${euros(Math.round(dish.price * 100))} a porzione</span>
                    <div class="quantity-control" role="group" aria-label="Quantità di ${escapeHTML(dish.name)}">
                        <button type="button" data-cart-action="decrease" data-id="${id}" aria-label="Riduci quantità di ${escapeHTML(dish.name)}">${icon('minus')}</button>
                        <output aria-label="${count} porzioni">${count}</output>
                        <button type="button" data-cart-action="increase" data-id="${id}" aria-label="Aumenta quantità di ${escapeHTML(dish.name)}" ${count >= MAX_QUANTITY ? 'disabled' : ''}>${icon('plus')}</button>
                    </div>
                </div>
                <span class="cart-item-price">${euros(Math.round(dish.price * 100) * count)}</span>
                <button type="button" class="cart-remove" data-cart-action="remove" data-id="${id}" aria-label="Rimuovi ${escapeHTML(dish.name)}">${icon('close')}</button>
            </li>`;
        }).join('');
        $('[data-summary="subtotal"]').textContent = euros(subtotal);
        $('[data-summary="shipping"]').textContent = shipping ? euros(shipping) : mode === 'pickup' ? 'Gratuito' : 'Gratuita';
        $('[data-summary="shipping-label"]').textContent = mode === 'delivery' ? 'Consegna' : 'Ritiro in osteria';
        $('[data-summary="total"]').textContent = euros(total);
        $('[data-summary="shipping-note"]').textContent = mode === 'delivery' && subtotal < 5000 ? `Mancano ${euros(5000 - subtotal)} per la consegna gratuita.` : mode === 'delivery' ? 'La consegna è offerta da noi.' : 'Asporto gratuito · pronto in 20–30 minuti.';
        $('#drawer-mode').innerHTML = `${icon(mode === 'delivery' ? 'truck' : 'takeaway')}<span>${mode === 'delivery' ? 'Consegna a Parma e dintorni' : 'Ritiro in osteria'}</span><button type="button" data-change-mode>${mode === 'delivery' ? 'Preferisco l’asporto' : 'Preferisco la consegna'}</button>`;
        $$('[name="order-mode"]').forEach(input => { input.checked = input.value === mode; });
        $('[data-delivery-field]').hidden = mode !== 'delivery';
        $('#co-address').required = mode === 'delivery';
        $('#co-address').disabled = mode !== 'delivery';
        $('#payment-label').textContent = mode === 'delivery' ? 'Alla consegna' : 'Al ritiro';
        if (focusTarget) {
            const sameButton = $(`[data-cart-action="${focusTarget.action}"][data-id="${focusTarget.id}"]`, items);
            (sameButton && !sameButton.disabled ? sameButton : $('[data-cart-action]', items) || $('#checkout-button')).focus({ preventScroll: true });
        }
    }

    function setCheckout(value) {
        isCheckout = value;
        $('#checkout-form').hidden = !value;
        $('#cart-items').hidden = value;
        $('#drawer-foot').hidden = value;
        renderCart();
        $('.cart-scroll').scrollTo({ top: 0 });
        if (value) $('#co-name').focus({ preventScroll: true });
    }

    function openCart() {
        setCheckout(false);
        openDialog($('#cart-drawer'));
    }

    function initCart() {
        loadCart();
        renderCart();
        $$('[data-cart-open]').forEach(button => button.addEventListener('click', openCart));
        $$('[data-add]').forEach(button => button.addEventListener('click', () => {
            const id = button.dataset.add;
            if (!Object.hasOwn(catalog, id)) return;
            const current = cart.get(id) || 0;
            if (current >= MAX_QUANTITY) { notify('Massimo 20 porzioni per piatto. Per grandi gruppi chiamateci.'); return; }
            cart.set(id, current + 1);
            persistCart();
            renderCart();
            notify(`${catalog[id].name} aggiunto al vostro ordine.`, true);
            if (gsap && !motionQuery.matches) {
                gsap.fromTo($('.cart-count'), { scale: 0.6 }, { scale: 1, duration: 0.45, ease: 'back.out(2)' });
                gsap.fromTo($('.icon', button), { rotation: 0 }, { rotation: 180, duration: 0.65, ease: 'power2.out' });
            }
        }));
        $('#cart-items').addEventListener('click', event => {
            const button = event.target.closest('[data-cart-action]');
            if (!button) return;
            const { id, cartAction: action } = button.dataset;
            if (!cart.has(id)) return;
            const count = cart.get(id);
            if (action === 'remove' || (action === 'decrease' && count === 1)) cart.delete(id);
            else if (action === 'increase') cart.set(id, Math.min(MAX_QUANTITY, count + 1));
            else if (action === 'decrease') cart.set(id, count - 1);
            persistCart();
            renderCart({ action, id });
        });
        $$('[name="order-mode"]').forEach(input => input.addEventListener('change', () => {
            mode = input.value;
            persistCart();
            renderCart();
        }));
        $('#drawer-mode').addEventListener('click', event => {
            if (!event.target.closest('[data-change-mode]')) return;
            mode = mode === 'delivery' ? 'pickup' : 'delivery';
            persistCart();
            renderCart();
            $('[data-change-mode]').focus({ preventScroll: true });
        });
        $('#checkout-button').addEventListener('click', () => { if (cart.size) setCheckout(true); });
        $('.checkout-back').addEventListener('click', () => { setCheckout(false); $('#checkout-button').focus(); });
        $('#checkout-form').addEventListener('submit', event => {
            event.preventDefault();
            const form = event.currentTarget;
            if (!cart.size || !validateForm(form)) return;
            const data = new FormData(form);
            const { quantity, total } = totals();
            const firstName = String(data.get('name')).trim().split(/\s+/)[0];
            const delivery = mode === 'delivery';
            const reference = `ODB-${String(Date.now()).slice(-6)}`;
            const button = $('[type="submit"]', form);
            const original = button.innerHTML;
            button.disabled = true;
            button.textContent = 'Prepariamo il riepilogo…';
            setTimeout(() => {
                cart.clear();
                persistCart();
                setCheckout(false);
                showSuccess({
                    eyebrow: 'Ordine dimostrativo completato',
                    title: 'Il sapore di casa, anche a casa.',
                    message: `Grazie, ${firstName}! La simulazione del vostro ordine è completata. Nessun pagamento è stato addebitato.`,
                    summary: [`${reference} · ${delivery ? 'Consegna' : 'Asporto'}`, `${quantity} ${quantity === 1 ? 'piatto' : 'piatti'} · Totale ${euros(total)}`, `Orario: ${data.get('time') === 'asap' ? delivery ? '35–50 minuti (indicativo)' : '20–30 minuti (indicativo)' : data.get('time')}`, 'Nessuna consegna o preparazione reale verrà effettuata.'],
                    returnFocus: $('.cart-toggle'),
                });
                form.reset();
                button.innerHTML = original;
                button.disabled = false;
            }, motionQuery.matches ? 0 : 550);
        });
        // Keep multiple tabs in sync, without ever storing personal information.
        window.addEventListener('storage', event => {
            if (event.key !== STORE_KEY) return;
            cart.clear();
            loadCart();
            if (!cart.size) isCheckout = false;
            setCheckout(isCheckout);
        });
    }

    /* ── FAQ accordion: at most one panel open, no layout jumps. ── */
    function toggleFAQ(button, open) {
        const panel = $(`#${button.getAttribute('aria-controls')}`);
        button.setAttribute('aria-expanded', String(open));
        gsap?.killTweensOf(panel);
        if (open) {
            panel.hidden = false;
            if (gsap && !motionQuery.matches) {
                gsap.fromTo(panel, { height: 0, opacity: 0 }, { height: 'auto', opacity: 1, duration: 0.5, ease: 'power2.out', clearProps: 'height,opacity', onComplete: refreshScroll });
            } else {
                panel.style.height = '';
                panel.style.opacity = '';
                refreshScroll();
            }
        } else {
            animate(panel, { height: 0, opacity: 0, duration: 0.4, onComplete: () => {
                if (button.getAttribute('aria-expanded') === 'false') panel.hidden = true;
                panel.style.height = '';
                panel.style.opacity = '';
                refreshScroll();
            } });
        }
        animate($('.icon', button), { rotation: open ? 45 : 0, duration: 0.45 });
    }

    function initFAQ() {
        $$('.faq-question').forEach(button => button.addEventListener('click', () => {
            const open = button.getAttribute('aria-expanded') !== 'true';
            $$('.faq-question[aria-expanded="true"]').forEach(other => { if (other !== button) toggleFAQ(other, false); });
            toggleFAQ(button, open);
        }));
    }

    /* ── Newsletter and transparent demo/legal information. ── */
    function initNewsletter() {
        $('#newsletter-form').addEventListener('submit', event => {
            event.preventDefault();
            const input = $('#newsletter-email');
            const message = $('#newsletter-message');
            const valid = input.value.trim() && input.validity.valid;
            message.hidden = false;
            message.classList.toggle('is-error', !valid);
            input.setAttribute('aria-invalid', String(!valid));
            input.setAttribute('aria-describedby', message.id);
            if (!valid) {
                message.textContent = 'Inserite un indirizzo email valido.';
                input.focus();
                return;
            }
            message.textContent = 'Grazie! Iscrizione dimostrativa completata. Nessun indirizzo è stato salvato.';
            event.currentTarget.reset();
            if (gsap && !motionQuery.matches) gsap.fromTo(message, { y: 6, opacity: 0 }, { y: 0, opacity: 1, duration: 0.5 });
        });
    }

    function initLegal() {
        const content = {
            privacy: {
                title: 'La vostra privacy',
                paragraphs: [
                    'Questo sito è un prototipo dimostrativo della Heritage Collection. I moduli di prenotazione, ordine e newsletter sono simulati: non vengono effettuate chiamate a servizi esterni né inviati messaggi email.',
                    'Nome, telefono, email, indirizzo e note rimangono esclusivamente nella memoria temporanea della pagina. Non vengono salvati nel carrello locale, trasmessi, o registrati in una banca dati. Il ricaricamento della pagina interrompe le richieste in corso.',
                    'Solo le scelte del carrello (codici dei piatti, quantità e modalità di ritiro) vengono conservate nel dispositivo tramite localStorage, per non perdere l’ordine durante la navigazione. Potete rimuoverle svuotando il carrello o cancellando i dati del sito dal browser.',
                    'Prima di un utilizzo commerciale reale sarà necessario inserire i dati del titolare, i recapiti corretti e un’informativa privacy appropriata.',
                ],
            },
            note: {
                title: 'Note legali',
                paragraphs: [
                    'Osteria del Borgo è un’identità immaginaria, ideata per presentare un’esperienza digitale dedicata alla ristorazione tradizionale italiana. Qualsiasi somiglianza con attività esistenti è casuale.',
                    'Storia, nomi, chef, indirizzo, contatti, orari, etichette dei vini, recensioni e prezzi sono fittizi. Le fotografie e i ritratti sono illustrativi, non documentano un ristorante o clienti reali. Alcune immagini sono state create digitalmente per questo progetto.',
                    'Prenotazioni, ordini, pagamenti e iscrizioni non hanno effetti reali. Nessun importo viene addebitato. La mappa è illustrativa; il link alle indicazioni apre soltanto il centro storico di Parma. I collegamenti social aprono le rispettive piattaforme, non profili del ristorante.',
                    'Prima della pubblicazione commerciale, sostituire i contenuti dimostrativi con informazioni verificate e fotografie della struttura.',
                ],
            },
            cookie: {
                title: 'Cookie e memoria locale',
                paragraphs: [
                    'Non utilizziamo cookie pubblicitari, strumenti di analisi, pixel di tracciamento o mappe incorporate. Le immagini, i caratteri tipografici e le librerie vengono caricati dal sito stesso.',
                    'Il solo archivio locale è “osteria-del-borgo:cart:v1”: contiene i codici dei piatti, le quantità e la modalità di ordine. Serve a conservare il carrello sul vostro dispositivo e non contiene dati personali.',
                    'Potete cancellare queste informazioni dalle impostazioni del browser. I link a mappe e social aprono siti di terzi, che applicano le proprie informative.',
                ],
            },
        };
        $$('[data-legal]').forEach(button => button.addEventListener('click', () => {
            const page = content[button.dataset.legal];
            $('#legal-title').textContent = page.title;
            $('#legal-body').replaceChildren(...page.paragraphs.map(text => {
                const p = document.createElement('p');
                p.textContent = text;
                return p;
            }));
            openDialog($('#legal-dialog'));
        }));
    }

    /* ── Motion direction: slow, warm and restrained. ── */
    function splitTitle(title) {
        const text = title.innerText.replace(/\s+/g, ' ').trim();
        title.setAttribute('aria-label', text);
        const walker = document.createTreeWalker(title, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        nodes.forEach(node => {
            const fragment = document.createDocumentFragment();
            node.textContent.split(/(\s+)/).forEach(word => {
                if (!word.trim()) { fragment.append(document.createTextNode(word)); return; }
                const clip = document.createElement('span');
                clip.className = 'word-clip';
                clip.setAttribute('aria-hidden', 'true');
                const inner = document.createElement('span');
                inner.className = 'split-word';
                inner.textContent = word;
                clip.append(inner);
                fragment.append(clip);
            });
            node.replaceWith(fragment);
        });
        return $$('.split-word', title);
    }

    function initMotion() {
        if (!gsap || !ScrollTrigger) return;
        gsap.registerPlugin(ScrollTrigger);
        // A media context reverts all animations when reduced motion is enabled.
        const mm = gsap.matchMedia();
        mm.add('(prefers-reduced-motion: no-preference)', () => {
            if (window.Lenis && finePointer.matches) {
                lenis = new Lenis({ duration: 1.15, smoothWheel: true, syncTouch: false, wheelMultiplier: 0.85 });
                lenis.on('scroll', ScrollTrigger.update);
                const tick = time => lenis?.raf(time * 1000);
                gsap.ticker.add(tick);
                gsap.ticker.lagSmoothing(0);
                // Included in this context's cleanup below.
                lenis._heritageTick = tick;
            }

            // Preserve the visual typography and expose an unsplit accessible name.
            $$('[data-split]').forEach(title => {
                const words = $$('.split-word', title).length ? $$('.split-word', title) : splitTitle(title);
                if (title.closest('.hero')) return;
                gsap.from(words, {
                    yPercent: 110, opacity: 0, duration: 1.05, stagger: 0.035, ease: 'power3.out',
                    scrollTrigger: { trigger: title, start: 'top 89%', once: true },
                });
            });
            $$('[data-reveal]').forEach(element => {
                // Hidden tab content must stay visible when its tab is opened later.
                if (element.closest('.menu-panel')) return;
                gsap.from(element, {
                    y: 28, opacity: 0, duration: 0.95, ease: 'power2.out',
                    scrollTrigger: { trigger: element, start: 'top 90%', once: true },
                });
            });
            const hero = gsap.timeline({ defaults: { ease: 'power3.out' }, delay: 0.08 });
            hero.from('.hero-eyebrow', { y: 12, opacity: 0, duration: 0.9 })
                .from('.hero-title .split-word', { yPercent: 105, opacity: 0, duration: 1.25, stagger: 0.065 }, '-=0.65')
                .from('.hero-subtitle', { y: 14, opacity: 0, duration: 1 }, '-=0.9')
                .from('.hero-actions .btn', { y: 14, opacity: 0, duration: 0.8, stagger: 0.12 }, '-=0.75')
                .from('.hero-bottom,.hero-seal', { opacity: 0, duration: 1.1 }, '-=0.6');

            // Modest translate-only parallax on large screens, never on mobile.
            if (window.innerWidth > 900) {
                $$('[data-parallax]').forEach(element => {
                    const distance = Number(element.dataset.parallax) * 250;
                    gsap.fromTo(element, { y: -distance }, {
                        y: distance, ease: 'none',
                        scrollTrigger: { trigger: element.closest('section'), start: 'top bottom', end: 'bottom top', scrub: 1.6 },
                    });
                });
            }

            // Ambient particles only animate while the hero is in view.
            const dust = $('#hero-dust');
            dust.replaceChildren();
            const dustTimeline = gsap.timeline({ repeat: -1, paused: true });
            const count = window.innerWidth < 680 ? 9 : 18;
            for (let index = 0; index < count; index++) {
                const particle = document.createElement('span');
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${20 + Math.random() * 75}%`;
                dust.append(particle);
                dustTimeline.fromTo(particle, { y: 25, x: 0, opacity: 0 }, {
                    y: -75 - Math.random() * 80, x: (Math.random() - 0.5) * 70,
                    opacity: 0.15 + Math.random() * 0.35, duration: 12 + Math.random() * 8,
                    ease: 'sine.inOut', repeat: 1, yoyo: true,
                }, Math.random() * 5);
            }
            ScrollTrigger.create({ trigger: '#home', start: 'top bottom', end: 'bottom top', onToggle: self => self.isActive ? dustTimeline.play() : dustTimeline.pause() });
            const flame = gsap.to('.candle-flame', { scaleX: 0.88, scaleY: 1.08, rotation: 4, opacity: 0.78, duration: 0.7, ease: 'sine.inOut', repeat: -1, yoyo: true, paused: true });
            const glow = gsap.to('.candle-glow', { opacity: 0.5, scale: 1.15, duration: 1.7, ease: 'sine.inOut', repeat: -1, yoyo: true, paused: true });
            ScrollTrigger.create({ trigger: '.hours-card', start: 'top bottom', end: 'bottom top', onToggle: self => { self.isActive ? flame.play() : flame.pause(); self.isActive ? glow.play() : glow.pause(); } });
            const onVisibility = () => {
                if (document.hidden) { dustTimeline.pause(); flame.pause(); glow.pause(); }
                else ScrollTrigger.refresh();
            };
            document.addEventListener('visibilitychange', onVisibility);
            return () => {
                if (lenis) {
                    gsap.ticker.remove(lenis._heritageTick);
                    lenis.destroy();
                    lenis = undefined;
                }
                document.removeEventListener('visibilitychange', onVisibility);
                dust.replaceChildren();
            };
        });

        // Statistics run once and never depend on the animated text to be meaningful.
        $$('[data-count]').forEach(element => {
            const target = Number(element.dataset.count);
            const suffix = element.dataset.suffix || '';
            element.setAttribute('aria-label', `${target}${suffix}`);
            if (motionQuery.matches) { element.textContent = `${target}${suffix}`; return; }
            const state = { value: 0 };
            gsap.to(state, {
                value: target, duration: 2, ease: 'power2.out',
                scrollTrigger: { trigger: element, start: 'top 92%', once: true },
                onUpdate: () => { element.textContent = `${Math.round(state.value)}${suffix}`; },
            });
        });
        initHovers();
    }

    function initHovers() {
        if (!gsap || !finePointer.matches || motionQuery.matches) return;
        $$('.btn,.icon-btn,.dish-add').forEach(button => {
            const color = getComputedStyle(button).color;
            const background = getComputedStyle(button).backgroundColor;
            const border = getComputedStyle(button).borderColor;
            button.addEventListener('pointerenter', () => {
                if (button.disabled) return;
                animate(button, { backgroundColor: '#a77946', borderColor: '#a77946', color: '#f5efe6', boxShadow: '0 7px 25px rgba(167,121,70,.18)', duration: 0.45 });
                if (button.classList.contains('btn')) animate($('.icon', button), { x: 3, duration: 0.4 });
            });
            button.addEventListener('pointerleave', () => {
                animate(button, { backgroundColor: background, borderColor: border, color, boxShadow: '0 0 0 rgba(167,121,70,0)', duration: 0.65 });
                if (button.classList.contains('btn')) animate($('.icon', button), { x: 0, duration: 0.5 });
            });
        });
        $$('.dish-card').forEach(card => {
            const image = $('img', card);
            card.addEventListener('pointerenter', () => {
                animate(card, { y: -5, boxShadow: '0 18px 36px rgba(59,42,34,.10)', duration: 0.65 });
                animate(image, { scale: 1.045, duration: 1.1 });
            });
            card.addEventListener('pointerleave', () => {
                animate(card, { y: 0, boxShadow: '0 0 0 rgba(59,42,34,0)', duration: 0.75 });
                animate(image, { scale: 1, duration: 1.25 });
            });
        });
        $$('.gallery-item').forEach(card => {
            card.addEventListener('pointerenter', () => animate($('img', card), { scale: 1.045, duration: 1.5 }));
            card.addEventListener('pointerleave', () => animate($('img', card), { scale: 1, duration: 1.5 }));
        });
        $$('.text-link,.footer-col a,.socials a').forEach(link => {
            const color = getComputedStyle(link).color;
            link.addEventListener('pointerenter', () => animate(link, { color: '#a77946', duration: 0.4 }));
            link.addEventListener('pointerleave', () => animate(link, { color, duration: 0.6 }));
        });
        const light = $('.hero-light');
        const moveX = gsap.quickTo(light, 'x', { duration: 2, ease: 'power2.out' });
        const moveY = gsap.quickTo(light, 'y', { duration: 2, ease: 'power2.out' });
        $('#home').addEventListener('pointermove', event => {
            if (motionQuery.matches) return;
            moveX((event.clientX / window.innerWidth - 0.5) * 45);
            moveY((event.clientY / window.innerHeight - 0.5) * 25);
        }, { passive: true });
        $('#home').addEventListener('pointerleave', () => { moveX(0); moveY(0); });
    }

    /* ── Boot: independent, ordered modules; everything is available offline. ── */
    initDialogs();
    initNavigation();
    initMenu();
    initCarousels();
    initLightbox();
    initFieldFeedback();
    initBooking();
    initCart();
    initFAQ();
    initNewsletter();
    initLegal();
    // Forms are inert in the raw HTML; enable them only after handlers are ready.
    $$('form button[type="submit"]').forEach(button => { button.disabled = false; });
    initMotion();
    if (!gsap || !ScrollTrigger) $$('[data-count]').forEach(element => { element.textContent = element.dataset.count + (element.dataset.suffix || ''); });
    document.fonts.ready.then(refreshScroll);
    window.addEventListener('load', refreshScroll, { once: true });
    motionQuery.addEventListener('change', () => {
        if (gallerySwiper) gallerySwiper.params.speed = motionQuery.matches ? 0 : 850;
        if (reviewsSwiper) reviewsSwiper.params.speed = motionQuery.matches ? 0 : 850;
    });
})();
