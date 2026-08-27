---
name: design-system-
description: Creates implementation-ready design-system guidance with tokens, component behavior, and accessibility standards. Use when creating or updating UI rules, component specifications, or design-system documentation.
---

<!-- TYPEUI_SH_MANAGED_START -->

# داشبورد

## Mission
Deliver implementation-ready design-system guidance for داشبورد that can be applied consistently across dashboard web app interfaces.

## Brand
- Product/brand: داشبورد
- URL: https://sgharanie.ir/wp-admin
- Audience: authenticated users and operators
- Product surface: dashboard web app

## Style Foundations
- Visual style: structured, accessible, implementation-first
- Main font style: `font.family.primary=Montserrat Latin`, `font.family.stack=Montserrat Latin, Vazirmatn, ui-sans-serif, system-ui, sans-serif`, `font.size.base=15px`, `font.weight.base=400`, `font.lineHeight.base=25.5px`
- Typography scale: `font.size.xs=12px`, `font.size.sm=13px`, `font.size.md=15px`, `font.size.lg=26px`
- Color palette: `color.text.primary=#f2f2f2`, `color.text.secondary=#999999`, `color.text.tertiary=#8c99d9`, `color.text.inverse=#ffffff`, `color.surface.base=#000000`, `color.surface.muted=#263373`, `color.surface.raised=#0d1126`, `color.surface.strong=#333333`, `color.border.strong=#4055bf`
- Spacing scale: `space.1=2px`, `space.2=3px`, `space.3=4px`, `space.4=7px`, `space.5=8px`, `space.6=9px`, `space.7=10px`, `space.8=13px`
- Radius/shadow/motion tokens: `radius.xs=9px`, `radius.sm=10px`, `radius.md=14px`, `radius.lg=18px`, `radius.xl=999px` | `motion.duration.instant=180ms`, `motion.duration.fast=200ms`

## Accessibility
- Target: WCAG 2.2 AA
- Keyboard-first interactions required.
- Focus-visible rules required.
- Contrast constraints required.

## Writing Tone
concise, confident, implementation-focused

## Rules: Do
- Use semantic tokens, not raw hex values in component guidance.
- Every component must define required states: default, hover, focus-visible, active, disabled, loading, error.
- Responsive behavior and edge-case handling should be specified for every component family.
- Accessibility acceptance criteria must be testable in implementation.

## Rules: Don't
- Do not allow low-contrast text or hidden focus indicators.
- Do not introduce one-off spacing or typography exceptions.
- Do not use ambiguous labels or non-descriptive actions.

## Guideline Authoring Workflow
1. Restate design intent in one sentence.
2. Define foundations and tokens.
3. Define component anatomy, variants, and interactions.
4. Add accessibility acceptance criteria.
5. Add anti-patterns and migration notes.
6. End with QA checklist.

## Required Output Structure
- Context and goals
- Design tokens and foundations
- Component-level rules (anatomy, variants, states, responsive behavior)
- Accessibility requirements and testable acceptance criteria
- Content and tone standards with examples
- Anti-patterns and prohibited implementations
- QA checklist

## Component Rule Expectations
- Include keyboard, pointer, and touch behavior.
- Include spacing and typography token requirements.
- Include long-content, overflow, and empty-state handling.

## Quality Gates
- Every non-negotiable rule must use "must".
- Every recommendation should use "should".
- Every accessibility rule must be testable in implementation.
- Prefer system consistency over local visual exceptions.

<!-- TYPEUI_SH_MANAGED_END -->
