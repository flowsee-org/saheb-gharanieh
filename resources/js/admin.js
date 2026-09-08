/**
 * Saheb Gharaniyeh Cafe — panel behaviours.
 *
 *  · confirm dialog for destructive buttons   · drag-to-reorder categories
 *  · auto-dismissing notices                  · quick price editing
 *  · self-submitting filters                  · bulk selection bar
 *  · glyph picker summary                     · image pick / preview / clear
 *
 * Every one of these is an enhancement. The panel is built out of real forms
 * that post and reload, so with JavaScript off nothing here is missed except
 * convenience: the filter form keeps its «اعمال» button, the ↑/↓ buttons still
 * reorder, and a delete still deletes — just without being asked twice.
 *
 * Loaded as a module, so it runs after the document is parsed. Each block
 * therefore queries straight away and returns quietly when its hook is absent —
 * most pages carry only two or three of these.
 */

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ------------------------------------------------------------------ */
/*  Small shared helpers                                               */
/* ------------------------------------------------------------------ */

const FA_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

/**
 * Persian numerals, matching what App\Support\Persian::digits() renders, so a
 * count the JS updates does not switch script mid-sentence.
 *
 * @param {number|string} value
 * @returns {string}
 */
function faDigits(value) {
    return String(value).replace(/\d/g, (digit) => FA_DIGITS[digit]);
}

/**
 * Submit a form as if `button` had been clicked, so its name/value reaches the
 * server — the bulk bar tells activate from delete by nothing else.
 *
 * requestSubmit() throws when the submitter is not a descendant of the form,
 * which is the normal case here: several confirm buttons sit inside one form
 * and point at another through `form="…"`. Those get a plain submit.
 *
 * @param {HTMLFormElement} form
 * @param {HTMLElement|null} button
 */
function submitWith(form, button) {
    const submitter = button && form.contains(button) ? button : null;

    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit(submitter ?? undefined);
        return;
    }

    // Older engines: carry the submitter's name across by hand.
    if (submitter?.name) {
        const carrier = document.createElement('input');
        carrier.type = 'hidden';
        carrier.name = submitter.name;
        carrier.value = submitter.value;
        form.appendChild(carrier);
    }

    form.submit();
}

/* ------------------------------------------------------------------ */
/*  Confirm dialog                                                     */
/* ------------------------------------------------------------------ */

/*
 * One dialog per page, borrowed by every [data-confirm] button. The click is
 * swallowed and replayed on accept rather than mirrored into a second form, so
 * whatever the button was going to post is exactly what gets posted.
 */
const confirmBox = document.getElementById('admin-confirm');

if (confirmBox) {
    const confirmText = confirmBox.querySelector('[data-confirm-text]');
    const acceptButton = confirmBox.querySelector('[data-confirm-accept]');

    let pendingForm = null;
    let pendingButton = null;
    let returnFocusTo = null;

    /**
     * @param {HTMLElement} button the [data-confirm] control that was clicked
     * @param {HTMLFormElement} form the form it will submit
     */
    function openConfirm(button, form) {
        pendingForm = form;
        pendingButton = button;
        returnFocusTo = document.activeElement;

        if (confirmText) confirmText.textContent = button.dataset.confirm || '';

        confirmBox.hidden = false;
        acceptButton?.focus();
    }

    function closeConfirm() {
        confirmBox.hidden = true;
        pendingForm = null;
        pendingButton = null;

        // Back to the button that opened it, so a keyboard user keeps their place.
        returnFocusTo?.focus?.();
        returnFocusTo = null;
    }

    document.addEventListener('click', (event) => {
        const button = event.target.closest?.('[data-confirm]');
        if (!button) return;

        // .form resolves both a button inside a form and one bound by form="…".
        const form = button.form;
        if (!form) return;

        event.preventDefault();
        openConfirm(button, form);
    });

    confirmBox.addEventListener('click', (event) => {
        if (event.target.closest('[data-confirm-cancel]')) {
            closeConfirm();
            return;
        }

        if (event.target.closest('[data-confirm-accept]') && pendingForm) {
            const form = pendingForm;
            const button = pendingButton;

            closeConfirm();
            submitWith(form, button);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (confirmBox.hidden) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            closeConfirm();
            return;
        }

        // Hold Tab inside the dialog: the page behind it is not answerable yet.
        if (event.key !== 'Tab') return;

        const stops = [...confirmBox.querySelectorAll('button:not([hidden])')];
        if (stops.length === 0) return;

        const edge = event.shiftKey ? stops[0] : stops[stops.length - 1];

        if (document.activeElement === edge || !confirmBox.contains(document.activeElement)) {
            event.preventDefault();
            (event.shiftKey ? stops[stops.length - 1] : stops[0]).focus();
        }
    });
}

/* ------------------------------------------------------------------ */
/*  Notices                                                            */
/* ------------------------------------------------------------------ */

/*
 * The toasts are already on the page — the server printed them. All that is
 * added here is a way for them to leave.
 */
const TOAST_LIFE = 5200;

document.querySelectorAll('[data-toast]').forEach((toast) => {
    /** Fade out if that is welcome, otherwise just go. */
    function dismiss() {
        if (prefersReducedMotion) {
            toast.hidden = true;
            return;
        }

        toast.dataset.leaving = '';
        setTimeout(() => {
            toast.hidden = true;
        }, 240);
    }

    toast.querySelector('[data-toast-close]')?.addEventListener('click', dismiss);

    // A list of validation errors waits to be read; a "saved" notice does not.
    if (toast.hasAttribute('data-toast-sticky')) return;

    // Someone who asked for less movement gets no timed disappearance either —
    // a notice that vanishes on its own is exactly the kind of surprise meant.
    if (prefersReducedMotion) return;

    setTimeout(dismiss, TOAST_LIFE);
});

/* ------------------------------------------------------------------ */
/*  Filters                                                            */
/* ------------------------------------------------------------------ */

/*
 * A GET form, so every result set keeps its own bookmarkable URL. Changing a
 * select just submits it; the «اعمال» button exists for when this file does not
 * run, and is taken away as soon as it does.
 */
const filterForm = document.querySelector('[data-filters]');

if (filterForm) {
    filterForm.querySelector('.admin-filter-apply')?.setAttribute('hidden', '');

    filterForm.querySelectorAll('[data-filter-submit]').forEach((control) => {
        control.addEventListener('change', () => {
            // No page input in the form, so a new filter starts back at page one.
            filterForm.requestSubmit();
        });
    });
}

/* ------------------------------------------------------------------ */
/*  Glyph picker                                                       */
/* ------------------------------------------------------------------ */

/*
 * The picker is native radios in a <details>, and the swatch grid shows the
 * choice by itself through :checked. Only the collapsed summary needs telling,
 * and the drawing it should show is already on the page — in the swatch — so it
 * is copied across rather than rebuilt.
 */
document.querySelectorAll('[data-glyph-picker]').forEach((picker) => {
    const preview = picker.querySelector('[data-glyph-preview]');
    const valueLabel = picker.querySelector('[data-glyph-value]');

    picker.addEventListener('change', (event) => {
        const option = event.target.closest?.('[data-glyph-option]');
        if (!option) return;

        if (valueLabel) valueLabel.textContent = option.dataset.glyphName || 'بدون نقش';

        const drawing = option.nextElementSibling?.querySelector('svg');
        if (!drawing || !preview) return;

        const copy = drawing.cloneNode(true);
        copy.setAttribute('class', 'h-5 w-5');
        preview.replaceChildren(copy);
    });
});

/* ------------------------------------------------------------------ */
/*  Image field                                                        */
/* ------------------------------------------------------------------ */

/**
 * Show the file that was just picked, and let it be taken back off again.
 *
 * `remove_image` is a flag on the same form rather than its own request, so
 * clearing the photo and saving the rest stays one action. Picking a file after
 * clearing has to put the flag back — otherwise the upload would arrive with an
 * instruction to delete it.
 *
 * Size is checked here as well as on the server. Not for safety — the server
 * decides — but because the alternative is uploading several megabytes over a
 * café's connection to be told no, and because a photo over post_max_size is
 * thrown away by PHP before Laravel can answer with anything useful.
 *
 * @param {HTMLElement} field the [data-image-field] wrapper
 */
function initImageField(field) {
    const input = field.querySelector('[data-image-input]');
    const preview = field.querySelector('[data-image-preview]');
    if (!input || !preview) return;

    const removeFlag = field.querySelector('[data-image-remove-flag]');
    const current = field.querySelector('[data-image-current]');
    const empty = field.querySelector('[data-image-empty]');
    const clearButton = field.querySelector('[data-image-clear]');
    const pickLabel = field.querySelector('[data-image-pick-label]');
    const errorText = field.querySelector('[data-image-error]');

    const maxBytes = Number(field.dataset.imageMaxBytes) || 0;
    const maxLabel = field.dataset.imageMaxLabel ?? '';

    let objectUrl = null;

    /** Release the last preview before making another; each one holds a blob. */
    function revoke() {
        if (!objectUrl) return;
        URL.revokeObjectURL(objectUrl);
        objectUrl = null;
    }

    /** Blank message clears the field; the server's own message arrives rendered. */
    function complain(message) {
        field.classList.toggle('admin-image--bad', Boolean(message));

        if (!errorText) return;
        errorText.textContent = message;
        errorText.hidden = !message;
    }

    input.addEventListener('change', () => {
        const file = input.files?.[0];

        revoke();
        complain('');

        if (!file) {
            preview.hidden = true;
            return;
        }

        if (maxBytes && file.size > maxBytes) {
            // Drop the file rather than leave it staged: the owner should not be
            // able to submit a form whose photo is already known to be refused.
            input.value = '';
            preview.hidden = true;
            preview.removeAttribute('src');
            complain(`حجم این تصویر بیشتر از ${maxLabel} مگابایت است. عکس کوچک‌تری انتخاب کنید.`);
            return;
        }

        objectUrl = URL.createObjectURL(file);
        preview.src = objectUrl;
        preview.hidden = false;

        if (empty) empty.hidden = true;
        if (removeFlag) removeFlag.value = '0';
        if (clearButton) clearButton.hidden = false;
        if (pickLabel) pickLabel.textContent = 'تصویر دیگر';
    });

    clearButton?.addEventListener('click', () => {
        revoke();
        complain('');

        input.value = '';
        preview.hidden = true;
        preview.removeAttribute('src');

        if (current) current.hidden = true;
        if (empty) empty.hidden = false;
        if (removeFlag) removeFlag.value = '1';
        if (pickLabel) pickLabel.textContent = 'انتخاب تصویر';

        clearButton.hidden = true;
    });
}

document.querySelectorAll('[data-image-field]').forEach(initImageField);

/* ------------------------------------------------------------------ */
/*  Drag to reorder                                                    */
/* ------------------------------------------------------------------ */

/**
 * Reorder the category rows by dragging their grip, then post the whole new
 * order at once.
 *
 * Deliberately mouse-only: the grip is aria-hidden and hidden below 768px,
 * because a touch drag inside a scrolling page is a fight, and the ↑/↓ buttons
 * beside every row already do the job one step at a time. Those buttons remain
 * the only path on a phone and the only path for a keyboard.
 *
 * @param {HTMLElement} list the [data-reorder] container
 */
function initReorder(list) {
    const form = document.getElementById(list.dataset.reorderForm || '');
    if (!form) return;

    const items = () => [...list.querySelectorAll('[data-reorder-item]')];
    const ids = () => items().map((item) => item.dataset.id);

    let dragged = null;
    let grabY = 0;
    let orderBefore = [];

    /** Put the row back where the pointer is and forget how far it had come. */
    function rebase(clientY) {
        grabY = clientY;
        dragged.style.transform = '';
    }

    function finish() {
        if (!dragged) return;

        dragged.style.transform = '';
        dragged.removeAttribute('data-dragging');
        list.removeAttribute('data-reordering');
        dragged = null;

        const orderAfter = ids();
        if (orderAfter.join() === orderBefore.join()) return;

        // The form ships nothing but a token until now; fill it and go.
        form.querySelectorAll('[data-reorder-id]').forEach((old) => old.remove());

        orderAfter.forEach((id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            input.setAttribute('data-reorder-id', '');
            form.appendChild(input);
        });

        form.requestSubmit();
    }

    list.addEventListener('pointerdown', (event) => {
        if (event.button !== 0 || event.pointerType === 'touch') return;

        const handle = event.target.closest?.('[data-reorder-handle]');
        if (!handle) return;

        dragged = handle.closest('[data-reorder-item]');
        if (!dragged) return;

        event.preventDefault();
        handle.setPointerCapture(event.pointerId);

        grabY = event.clientY;
        orderBefore = ids();

        dragged.setAttribute('data-dragging', '');
        list.setAttribute('data-reordering', '');
    });

    list.addEventListener('pointermove', (event) => {
        if (!dragged) return;

        dragged.style.transform = `translateY(${event.clientY - grabY}px)`;

        // Cross a neighbour's middle and swap with it, one row per move. The row
        // lands roughly under the pointer afterwards, so the offset resets.
        const previous = dragged.previousElementSibling;
        const next = dragged.nextElementSibling;

        if (event.clientY < grabY && previous?.matches('[data-reorder-item]')) {
            const bounds = previous.getBoundingClientRect();

            if (event.clientY < bounds.top + bounds.height / 2) {
                list.insertBefore(dragged, previous);
                rebase(event.clientY);
            }

            return;
        }

        if (event.clientY > grabY && next?.matches('[data-reorder-item]')) {
            const bounds = next.getBoundingClientRect();

            if (event.clientY > bounds.top + bounds.height / 2) {
                list.insertBefore(dragged, next.nextElementSibling);
                rebase(event.clientY);
            }
        }
    });

    list.addEventListener('pointerup', finish);
    list.addEventListener('pointercancel', finish);
}

document.querySelectorAll('[data-reorder]').forEach(initReorder);

/* ------------------------------------------------------------------ */
/*  Quick price                                                        */
/* ------------------------------------------------------------------ */

/*
 * Changing a price is the edit the owner makes most often, so each row carries
 * its own one-field form. Pressing Enter already submits it — a lone text input
 * beside a submit button needs no help — and this adds the other half: walking
 * away from a changed price saves it instead of losing it.
 *
 * Persian digits are left alone: QuickPriceRequest runs Persian::amount() on the
 * way in, so ۴۵٬۰۰۰ and 45000 arrive as the same number.
 */
document.querySelectorAll('[data-quick-price]').forEach((form) => {
    const input = form.querySelector('[data-price-input]');
    if (!input) return;

    const asTyped = input.value;
    let sent = false;

    form.addEventListener('submit', () => {
        sent = true;
    });

    input.addEventListener('blur', (event) => {
        if (sent || input.value === asTyped) return;

        // Clicking the row's own save button blurs the field first; let the click
        // through rather than posting twice.
        if (event.relatedTarget && form.contains(event.relatedTarget)) return;

        sent = true;
        form.requestSubmit();
    });
});

/* ------------------------------------------------------------------ */
/*  Bulk price adjustment                                               */
/* ------------------------------------------------------------------ */

/*
 * The price form's unit flips with its mode: درصد shows a percent sign,
 * مبلغ ثابت shows تومان. The radios already submit without help — this only
 * keeps the field's unit from lying about what will be applied.
 */
const bulkPriceForm = document.querySelector('.admin-bulk-price');

if (bulkPriceForm) {
    const modes = bulkPriceForm.querySelectorAll('input[name="mode"]');
    const unit = bulkPriceForm.querySelector('[data-amount-unit]');

    const syncUnit = () => {
        if (!unit) return;

        const checked = bulkPriceForm.querySelector('input[name="mode"]:checked');
        unit.textContent = checked?.value === 'fixed' ? 'تومان' : '٪';
    };

    modes.forEach((radio) => radio.addEventListener('change', syncUnit));
    syncUnit();
}

/* ------------------------------------------------------------------ */
/*  Bulk selection                                                     */
/* ------------------------------------------------------------------ */

/*
 * The bulk form holds no rows of its own: each row's checkbox joins it through
 * `form="bulk-form"`, which keeps the per-item forms as siblings instead of
 * illegally nested. So the bar can be revealed and hidden without touching the
 * list, and every ticked box is already part of the post.
 */
const bulkForm = document.querySelector('[data-bulk]');
const itemsList = document.querySelector('[data-items]');

if (bulkForm && itemsList) {
    const boxes = [...itemsList.querySelectorAll('[data-bulk-item]')];
    const selectAll = itemsList.querySelector('[data-bulk-all]');
    const counter = bulkForm.querySelector('[data-bulk-count]');

    /** Reflect how many rows are ticked into the bar and the select-all box. */
    function sync() {
        const chosen = boxes.filter((box) => box.checked).length;

        bulkForm.hidden = chosen === 0;

        if (counter) counter.textContent = faDigits(chosen);

        if (selectAll) {
            selectAll.checked = chosen > 0 && chosen === boxes.length;
            selectAll.indeterminate = chosen > 0 && chosen < boxes.length;
        }
    }

    boxes.forEach((box) => box.addEventListener('change', sync));

    selectAll?.addEventListener('change', () => {
        boxes.forEach((box) => {
            box.checked = selectAll.checked;
        });

        sync();
    });

    bulkForm.querySelector('[data-bulk-clear]')?.addEventListener('click', () => {
        boxes.forEach((box) => {
            box.checked = false;
        });

        sync();
    });

    // «انتقال» has nowhere to move the rows until a section is picked. The server
    // already refuses it (`required_if:action,category`); this only spares the trip.
    const destination = bulkForm.querySelector('[data-bulk-category]');
    const moveButton = bulkForm.querySelector('[name="action"][value="category"]');

    if (destination && moveButton) {
        const syncDestination = () => {
            moveButton.disabled = destination.value === '';
        };

        destination.addEventListener('change', syncDestination);
        syncDestination();
    }

    // A back-navigation can restore ticked boxes; start from what is really there.
    sync();
}
