# Design handoff requirements — mobile (UI/UX → build)

What to require from the UI/UX designer before we start building a mobile screen set. Tailored to the Flutter monorepo (`mobile/`) and its dual-theme, bilingual (Arabic RTL + English LTR) brand. Paste the relevant section to the designer when commissioning work.

The single most important ask: **deliver as a Figma file with Dev-Mode access — not exported images or PDF.** Dev Mode gives us exact spacing, color, type, and copy values to read directly, instead of re-deriving every number from a flat picture.

---

## 1. Format & access
- Deliver as a **Figma file with editable / Dev-Mode access** (link, not exported images or PDF).
- Organize into clearly named pages and frames (e.g. `Onboarding / Sign-in`, `Home`, `VVIP Benefits`). Consistent layer naming — handoff quality depends on it.
- State the **base frame size** designed at (e.g. 390 × 844 logical px) so we know the reference density.

## 2. Design tokens (as Figma variables/styles, not one-off values)
- **Color** — full palette as named styles (maroon/gold brand, surfaces, text, states). No loose hex per layer.
- **Typography** — the complete type scale as text styles (family, weight, size, line-height, letter-spacing).
- **Spacing & grid** — an 8-pt (or chosen) spacing system, plus corner-radius and elevation/shadow tokens.

These map directly onto the Flutter `ThemeData` — the difference between a clean theme and hundreds of hardcoded values.

## 3. Themes & localization (both required, not optional)
- **Light *and* dark** themes for every screen.
- **Arabic (RTL) *and* English (LTR)** for at least all key screens — so mirroring, text expansion, and Tajawal vertical alignment are designed for, not discovered in code.
- Use **real representative content**, including real Arabic strings — no lorem ipsum. Show long names / long labels so we see how they wrap.

## 4. Components
- Provide a **reusable component library** (buttons, inputs, cards, chips, nav bar, list items) as Figma components **with variants and states** (default / pressed / disabled / focused / error). These map 1:1 onto reusable Flutter widgets.

## 5. Screen states (for every screen)
- Empty, loading (skeletons preferred), error, and populated. Include fallbacks for missing images/avatars.

## 6. Motion & interaction
- Specs for key transitions, micro-interactions, and any animations (what moves, duration, easing). A short note or prototype per interaction is enough.

## 7. App shell & system assets
- **App icon** (with source), **splash screen**, and per-screen **status-bar treatment** (light/dark).
- **Safe-area / notch** handling shown on relevant screens.

## 8. Accessibility
- Minimum **touch-target sizes** (≥ 44–48 px), and **text/background contrast** meeting WCAG AA. Confirm layouts don't break at larger system font sizes.

## 9. Assets & fonts
- **Icons and illustrations as SVG** (or exportable Figma component sets).
- The actual **font files (all weights used)** with confirmation we're licensed to embed them in a shipped app.

## 10. Flow
- A simple **user-flow / navigation map** showing how screens connect.

---

## One-line version to send a designer

> Please deliver as a Figma file with editable / Dev-Mode access, using shared color + type + spacing variables, covering light & dark themes, Arabic (RTL) and English, all screen states, with a component library and icons as SVG.
