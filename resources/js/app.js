// Public registration form enhancements:
//  - live client-side "Latin (English) letters only" validation on flagged
//    fields, mirroring the server-side App\Rules\LatinText rule
//  - inline error highlighting + messages per field
//  - on load, when the server returned errors, scroll to the form card and
//    focus the first invalid field (so the page opens at the form, not the top)

// Arabic script blocks (U+0600–06FF, 0750–077F, 08A0–08FF, FB50–FDFF,
// FE70–FEFF). Keep in sync with App\Rules\LatinText.
const ARABIC_SCRIPT =
    /[؀-ۿݐ-ݿࢠ-ࣿﭐ-﷿ﹰ-﻿]/;

function fieldFor(input) {
    return input.closest('.lfc-field');
}

function showFieldError(input, message) {
    const field = fieldFor(input);
    if (!field) {
        return;
    }

    field.classList.add('lfc-field-error');
    input.setAttribute('aria-invalid', 'true');

    let messageEl = field.querySelector('.lfc-field-message');
    if (!messageEl) {
        messageEl = document.createElement('span');
        messageEl.className = 'lfc-field-message';
        field.appendChild(messageEl);
    }
    messageEl.textContent = message;
}

function clearFieldError(input) {
    const field = fieldFor(input);
    if (!field) {
        return;
    }

    field.classList.remove('lfc-field-error');
    input.removeAttribute('aria-invalid');
    field.querySelector('.lfc-field-message')?.remove();
}

function focusFirstError() {
    const card = document.getElementById('registration-form');
    card?.scrollIntoView({ block: 'start' });

    const firstInvalid = document.querySelector(
        '.lfc-field-error input, .lfc-field-error select, .lfc-field-error textarea',
    );
    firstInvalid?.focus({ preventScroll: true });
}

function initLatinInputs(form) {
    const latinInputs = Array.from(form.querySelectorAll('.js-latin-input'));

    latinInputs.forEach((input) => {
        input.addEventListener('input', () => {
            if (ARABIC_SCRIPT.test(input.value)) {
                showFieldError(input, input.dataset.latinMessage);
            } else {
                clearFieldError(input);
            }
        });
    });

    // Native validation handles empty required fields; this only guards the
    // Latin rule, which the browser cannot enforce on its own.
    form.addEventListener('submit', (event) => {
        const invalid = latinInputs.filter((input) => ARABIC_SCRIPT.test(input.value));

        if (invalid.length === 0) {
            return;
        }

        event.preventDefault();
        invalid.forEach((input) => showFieldError(input, input.dataset.latinMessage));
        focusFirstError();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.lfc-form');
    if (form) {
        initLatinInputs(form);
    }

    // Server-side validation failed and re-rendered the page: open at the form.
    if (document.querySelector('.lfc-field-error, .lfc-alert-error')) {
        focusFirstError();
    } else if (document.querySelector('.lfc-alert-success')) {
        // Surface the confirmation without making the reviewer scroll for it.
        document.getElementById('registration-form')?.scrollIntoView({ block: 'start' });
    }
});
