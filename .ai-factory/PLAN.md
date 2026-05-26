# Implementation Plan: theme-json Skill

Branch: main
Created: 2026-04-26

## Settings
- Testing: no
- Logging: n/a (skill is a markdown prompt, not executable code)
- Docs: no

## Overview

Create a new Claude Code skill at `.claude/skills/theme-json/` that:
1. Reads an HTML layout file (Figma plugin export or hand-coded)
2. Finds and parses linked CSS files for design tokens
3. Extracts colors, font families, font sizes, and layout values from `:root {}` + typography rules
4. Writes `theme.json` (v3, open editor) to the theme root
5. Appends a `:root {}` CSS custom properties block to `_variables.scss`

No clarification round — fully automatic. Modelled on the existing `acf-from-html` skill.

Key design decisions from exploration:
- The Figma plugin already outputs a clean `:root {}` "Design Tokens" block with semantic names
- Editor policy: open (custom: true) — users can pick any color/size beyond the palette
- `global-styles` is dequeued in this theme → `--wp--preset--*` vars do NOT reach the frontend
- The `:root {}` block added to `_variables.scss` IS the frontend design token source

## Tasks

### Phase 1: Core Skill File

- [x] Task 1: Create `.claude/skills/theme-json/SKILL.md`

  Main skill instruction file covering all steps:
  - Frontmatter: name, description, argument-hint, allowed-tools
  - Step 0: Load config.yaml → resolve theme root (default: `theme/vite-wordpress-starter-theme/`)
  - Step 1: Read HTML at given path → collect CSS sources (`<link rel="stylesheet">` + `<style>` tags)
  - Step 2: Parse CSS → extract `:root {}` custom properties + `h1–h6/body/btn/label` font-size rules
  - Step 3: Build token map — colors, font families, font sizes (px→rem, 16px base), layout contentSize
  - Step 4: Overwrite check for existing `theme.json` (AskUserQuestion if found)
  - Step 5: Write `theme.json` v3 using schema from `references/THEME-JSON-SCHEMA.md`
  - Step 6: Append `:root {}` block to `_variables.scss` with `/* theme-json: generated tokens */` marker; AskUserQuestion if marker already present
  - Step 7: Summary output + note about `global-styles` dequeue behaviour

  Files: `.claude/skills/theme-json/SKILL.md`

### Phase 2: Reference Files

- [x] Task 2: Create `.claude/skills/theme-json/references/THEME-JSON-SCHEMA.md`

  Reference for the theme.json v3 output structure:
  - Full annotated JSON skeleton with all fields the skill uses
  - Field explanations: slug naming rules, rem conversion (16px base), layout fields
  - WordPress CSS custom property names generated per token (`--wp--preset--color--<slug>`)
  - Note: those vars don't reach the frontend when `global-styles` is dequeued

  Files: `.claude/skills/theme-json/references/THEME-JSON-SCHEMA.md`

- [x] Task 3: Create `.claude/skills/theme-json/references/EXAMPLES.md`

  Worked end-to-end example using real web4med files already in the repo:
  - Input: `html-files/index.html` + `html-files/css/style.css`
  - Section 1: extracted token map — all 10 colors, 1 font family, 6 font sizes, 1 layout value
  - Section 2: complete generated `theme.json` with real values populated
  - Section 3: exact `:root {}` block appended to `_variables.scss`

  Files: `.claude/skills/theme-json/references/EXAMPLES.md`

## Commit Plan

_3 tasks — single commit at completion._

```
feat: add theme-json skill

Generates theme.json (v3) and _variables.scss tokens from a Figma-exported HTML layout.
Reads :root CSS custom properties and typography rules; no manual token entry needed.
```
