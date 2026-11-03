---
name: ui-ux-design
description: >-
  Premier UI/UX Design System and Modern Aesthetics Guide.
  Activate when designing, styling, or auditing web interfaces, executive dashboards,
  color palettes, typography, card layouts, and micro-interactions.
---

# Premier UI/UX & Design System Architecture

This skill equips Antigravity with world-class product design principles inspired by modern executive software (Linear, Stripe, Apple, Mercury, Vercel).

---

## 1. Core Philosophy: Eliminating "AI Design Clichés"

When generating or refactoring user interfaces, strictly avoid these common AI design mistakes:
- ❌ **The "Patchwork Quilt"**: Stacking 4-5 different card styles with disconnected colors (e.g., a bright orange card next to a yellow warning box, green checkmarks, and a dark black bar).
- ❌ **Duplicate Greeting/Hero Blocks**: Having an empty "Namaste / Welcome" box followed by another card repeating the exact same greeting.
- ❌ **Harsh High-Contrast Outlines**: Thick 1px/2px solid black or brightly colored borders around every paragraph.
- ❌ **Wasted Vertical Space**: Enormous padding (80px+) with low information density that forces the user to scroll endlessly.
- ❌ **Generic Primary Colors**: Raw `blue-500`, pure `#000000`, or harsh saturated alerts.

---

## 2. Design System Tokens & Aesthetics

### Color Harmony
- **Canvas / Background**: Subtle off-white `#f8fafc` or `#f9fafb` with high-contrast content cards.
- **Card Surfaces**: Clean `#ffffff` with soft borders (`border: 1px solid #e2e8f0` or `rgba(15, 23, 42, 0.06)`).
- **Dark Elements**: Reserved for high-value focal points or sidebar navigation (`#090e17` / `#0f172a`), not scattered randomly.
- **Accent Hierarchy**: Single primary brand accent (e.g., Warm Amber/Orange `#f97316` / `#ea580c`), supported by neutral slates (`#334155`, `#64748b`, `#94a3b8`).
- **Semantic Badges**: Soft ambient backgrounds with matching text (`bg-emerald-50 text-emerald-700 border-emerald-200/70` for positive statuses, `bg-amber-50 text-amber-800 border-amber-200/80` for warnings/priorities).

### Typography
- Modern geometric or humanistic sans-serifs: `Inter`, `-apple-system`, `SF Pro Display`, `Outfit`.
- Strict scale:
  - Page Title: `24px - 30px`, font-extrabold, tight letter spacing (`-0.025em`).
  - Section Headings: `14px - 16px`, font-bold, slate-900.
  - Body Text: `13px - 14px`, font-normal / font-medium, slate-600.
  - Microcopy & Badges: `10px - 11px`, font-bold / uppercase tracking-wider (`0.05em`).

### Layout & Composition
- **Unified Command Grids**: On widescreen (`lg:`), use multi-column grids (e.g., 5-column welcome + live telemetry / 7-column AI briefing) instead of vertically stacked monolithic cards.
- **Horizontal Signal Rows**: Display operational metrics or list items in compact horizontal rows with circular badge indicators and clear label/value pairs, avoiding squished horizontal paragraph boxes.
- **Priority Callouts**: Use subtle left-bordered ambient callouts (`border-left: 3.5px solid #f59e0b; background: #fffbeb`) rather than heavy stark containers.

---

## 3. Smooth Transitions & Micro-Interactions
- **Persistent Shell**: Sidebar and topbar remain mounted during navigation; never reload or jump.
- **Content Cross-Fade**: Transition page contents smoothly (`opacity: 0 -> 1; transform: translateY(4px) -> 0`) in 140-180ms using `cubic-bezier(0.16, 1, 0.3, 1)`.
- **Button Feedback**: Subtle scale on active press (`transform: scale(0.98)`).
