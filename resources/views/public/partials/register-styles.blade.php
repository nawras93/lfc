<style>
    @import url('https://fonts.googleapis.com/css2?family=Changa:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap');

    :root {
        /* Lusail SC system tokens — mirrors mobile LfcColors + Filament admin. */
        --lfc-navy-900: #06223C;
        --lfc-navy-800: #0B3059;
        --lfc-navy-700: #113F71;
        --lfc-navy-600: #1C5288;
        --lfc-navy-500: #2E659B;
        --lfc-gold: #C8A24A;
        --lfc-gold-bright: #E7C97C;
        --lfc-gold-deep: #9A7526;
        --lfc-light-bg: #F3F7FB;
        --lfc-surface: #FFFFFF;
        --lfc-surface-hi: #E9F0F7;
        --lfc-outline: #DCE7F1;
        --lfc-ink: #0D1E30;
        --lfc-ink-muted: #5A6E84;
        --lfc-success: #2E7D5B;
    }

    * {
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    body.lfc-page {
        min-height: 100vh;
        margin: 0;
        font-family: "Tajawal", ui-sans-serif, system-ui, sans-serif;
        color: var(--lfc-ink);
        /* Navy hero gradient + fanar diagrid — the system's signature surface. */
        background-color: var(--lfc-navy-900);
        background-image:
            repeating-linear-gradient(45deg, transparent 0 25px, rgb(200 162 74 / 5%) 25px 26px),
            repeating-linear-gradient(-45deg, transparent 0 25px, rgb(200 162 74 / 5%) 25px 26px),
            radial-gradient(1200px 620px at 50% -14%, #0A2A4B 0%, rgb(10 42 75 / 0%) 60%),
            linear-gradient(165deg, var(--lfc-navy-900) 0%, var(--lfc-navy-800) 52%, var(--lfc-navy-700) 100%);
        background-attachment: fixed;
    }

    a {
        color: inherit;
        text-decoration: none;
    }

    .lfc-shell {
        width: min(1180px, calc(100% - 2rem));
        margin: 0 auto;
        padding: 1.5rem 0 3rem;
    }

    /* ---- Topbar ---- */
    .lfc-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        color: #fff;
        margin-bottom: 2rem;
    }

    .lfc-brand-logo {
        height: 2.5rem;
        width: auto;
        display: block;
    }

    .lfc-topbar-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: end;
        align-items: center;
        gap: .75rem;
    }

    .lfc-season {
        padding: .55rem .9rem;
        border-radius: 999px;
        font-size: .85rem;
        color: var(--lfc-gold-bright);
        background: rgb(255 255 255 / 6%);
        border: 1px solid rgb(200 162 74 / 30%);
        backdrop-filter: blur(12px);
    }

    /* Flag-only language toggle — shows the target language (mirrors the mobile app). */
    .lfc-language-switch {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .4rem;
        border-radius: 999px;
        background: rgb(255 255 255 / 8%);
        border: 1px solid rgb(255 255 255 / 16%);
        backdrop-filter: blur(12px);
        transition: border-color 160ms ease, background-color 160ms ease;
    }

    .lfc-language-switch:hover {
        border-color: rgb(200 162 74 / 55%);
        background: rgb(255 255 255 / 12%);
    }

    .lfc-flag {
        display: block;
        width: 1.9rem;
        height: 1.3rem;
        border-radius: .25rem;
        object-fit: cover;
        box-shadow: 0 1px 3px rgb(0 0 0 / 35%);
    }

    /* ---- Layout ---- */
    /* Stacked: hero banner on top, form full-width below for more room. */
    .lfc-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    .lfc-hero-card,
    .lfc-form-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.75rem;
        box-shadow: 0 30px 80px rgb(3 14 28 / 45%);
    }

    /* ---- Hero card ---- */
    .lfc-hero-card {
        min-height: 100%;
        padding: 2.25rem;
        color: #fff;
        background:
            linear-gradient(140deg, rgb(255 255 255 / 8%), transparent 46%),
            linear-gradient(180deg, var(--lfc-navy-800) 0%, var(--lfc-navy-900) 100%);
        border: 1px solid rgb(200 162 74 / 22%);
    }

    /* No gold glow blob. `content: none` also suppresses the built app.css
       (.lfc-* @layer) version of this pseudo-element. */
    .lfc-hero-card::after {
        content: none;
        display: none;
    }

    .lfc-hero-kicker {
        margin: 0;
        font-family: "Changa", "Tajawal", ui-sans-serif, system-ui, sans-serif;
        text-transform: uppercase;
        letter-spacing: .16em;
        font-size: .78rem;
        font-weight: 700;
        color: var(--lfc-gold-bright);
    }

    .lfc-hero-card h2 {
        margin: .75rem 0 0;
        font-family: "Changa", "Tajawal", ui-sans-serif, system-ui, sans-serif;
        font-weight: 700;
        /* Full-width hero now has room — keep the title on one line.
           max-width:none overrides the built app.css (.lfc-* @layer) leftover. */
        max-width: none;
        font-size: clamp(1.5rem, 3.2vw, 2.9rem);
        line-height: 1.05;
        white-space: nowrap;
    }

    .lfc-hero-copy {
        /* max-width:none overrides the built app.css (.lfc-* @layer) leftover
           so the copy runs on one line where there is room. */
        max-width: none;
        margin-top: 1.2rem;
        font-size: 1.05rem;
        line-height: 1.8;
        color: rgb(234 242 250 / 82%);
    }

    .lfc-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        margin-top: 1.75rem;
    }

    .lfc-highlights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: .7rem;
        margin: 2rem 0 0;
        padding: 0;
        list-style: none;
    }

    .lfc-highlights li {
        position: relative;
        /* Logical padding so the bullet gutter flips correctly in RTL. */
        padding: .95rem 1rem;
        padding-inline-start: 2.6rem;
        border: 1px solid rgb(255 255 255 / 8%);
        border-radius: 1rem;
        background: rgb(255 255 255 / 5%);
        backdrop-filter: blur(10px);
    }

    .lfc-highlights li::before {
        content: "";
        position: absolute;
        inset-inline-start: 1rem;
        top: 50%;
        width: .55rem;
        height: .55rem;
        margin-top: -.275rem;
        border-radius: 2px;
        transform: rotate(45deg);
        background: var(--lfc-gold);
    }

    /* ---- Buttons ---- */
    .lfc-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 3rem;
        padding: .8rem 1.4rem;
        border: 0;
        border-radius: 999px;
        font: inherit;
        font-weight: 800;
        cursor: pointer;
        transition: transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
    }

    .lfc-button:hover {
        transform: translateY(-1px);
    }

    .lfc-button-primary {
        color: var(--lfc-navy-900);
        background: linear-gradient(135deg, var(--lfc-gold-bright) 0%, var(--lfc-gold) 100%);
        box-shadow: 0 12px 26px rgb(200 162 74 / 30%);
    }

    .lfc-button-secondary {
        color: #fff;
        border: 1px solid rgb(255 255 255 / 24%);
        background: rgb(255 255 255 / 8%);
    }

    /* ---- Form card ---- */
    .lfc-form-card {
        padding: 1.75rem;
        background: var(--lfc-light-bg);
        border: 1px solid var(--lfc-outline);
    }

    .lfc-alert {
        margin-bottom: 1rem;
        padding: 1rem 1.1rem;
        border-radius: 1rem;
    }

    .lfc-alert strong {
        display: block;
        margin-bottom: .35rem;
        font-family: "Changa", "Tajawal", ui-sans-serif, system-ui, sans-serif;
    }

    .lfc-alert-success {
        color: #0d5132;
        background: #dcfce7;
        border: 1px solid #86efac;
    }

    .lfc-alert-error {
        color: #7f1d1d;
        background: #fee2e2;
        border: 1px solid #fca5a5;
    }

    .lfc-alert-info {
        color: var(--lfc-navy-700);
        background: var(--lfc-surface-hi);
        border: 1px solid var(--lfc-outline);
    }

    .lfc-form {
        display: grid;
        gap: 1.2rem;
    }

    .lfc-form-section {
        padding: 1.25rem;
        border: 1px solid var(--lfc-outline);
        border-radius: 1.2rem;
        background: var(--lfc-surface);
    }

    .lfc-section-heading {
        margin-bottom: 1rem;
    }

    .lfc-section-heading h3 {
        margin: 0;
        font-family: "Changa", "Tajawal", ui-sans-serif, system-ui, sans-serif;
        font-size: 1.3rem;
        color: var(--lfc-navy-700);
    }

    .lfc-section-heading p {
        margin: .35rem 0 0;
        color: var(--lfc-ink-muted);
        line-height: 1.7;
    }

    .lfc-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .lfc-field {
        display: grid;
        gap: .45rem;
    }

    .lfc-field-full {
        grid-column: 1 / -1;
    }

    .lfc-field span {
        font-size: .95rem;
        font-weight: 700;
        color: var(--lfc-navy-700);
    }

    .lfc-field input,
    .lfc-field select {
        width: 100%;
        min-height: 3rem;
        padding: .82rem .95rem;
        border: 1px solid var(--lfc-outline);
        border-radius: .95rem;
        background: #fff;
        color: var(--lfc-ink);
        outline: none;
        font: inherit;
        transition: border-color 160ms ease, box-shadow 160ms ease;
    }

    .lfc-field input:focus,
    .lfc-field select:focus {
        border-color: var(--lfc-gold);
        box-shadow: 0 0 0 4px rgb(200 162 74 / 18%);
    }

    .lfc-checkbox {
        display: flex;
        gap: .8rem;
        align-items: flex-start;
        line-height: 1.8;
        color: var(--lfc-ink-muted);
    }

    .lfc-checkbox input {
        width: 1.1rem;
        height: 1.1rem;
        margin-top: .35rem;
        accent-color: var(--lfc-gold-deep);
    }

    .lfc-submit {
        width: 100%;
        min-height: 3.35rem;
    }

    @media (max-width: 960px) {
        .lfc-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .lfc-shell {
            width: min(100% - 1rem, 1180px);
            padding-top: 1rem;
        }

        .lfc-topbar {
            flex-direction: column;
            align-items: flex-start;
        }

        .lfc-topbar-actions {
            justify-content: flex-start;
        }

        .lfc-hero-card,
        .lfc-form-card {
            padding: 1.25rem;
            border-radius: 1.35rem;
        }

        .lfc-form-grid {
            grid-template-columns: 1fr;
        }

        .lfc-hero-card h2 {
            white-space: normal;
        }
    }
</style>
