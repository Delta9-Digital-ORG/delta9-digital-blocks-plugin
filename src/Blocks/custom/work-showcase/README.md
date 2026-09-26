# Work Showcase block

A full-bleed WebGL header that reveals a gallery of work tiles in 3D, ported to
Eightshift from the reference project `prag-matt-ic/imagen-header-rebuild`.

`eightshift-boilerplate/work-showcase` · dynamic · category **eightshift** ·
supports `align: full`, `anchor`, `spacing.margin`.

## Architecture

Vanilla three.js (not R3F — WordPress ships React 18 as `wp-element`, R3F 9 needs
React 19). The scene is a plain `Scene` class that mounts in **both** the front
end and the editor iframe (every `window`/`document` reference goes through
`container.ownerDocument`). WebGPU renders through `WebGPURenderer`; when WebGPU
is unavailable it falls back to WebGL 2 via `forceWebGL` — TSL compiles to GLSL,
so the same graph renders on both paths.

Tiles are **server-rendered as real `<img>`** (SEO, alt text, no-JS,
reduced-motion, free editor preview). `assets/index.js` lazy-imports the scene
when the block nears the viewport, mounts the canvas over the tiles, and fades
the tiles out (they stay in the DOM). It bails entirely on
`prefers-reduced-motion` or Save-Data.

## The slot table — one contract, three consumers

`slots.json` (camera `z 6 / fov 70`, a desktop table of 8 and a mobile table of
4, breakpoint 768) is read by:

1. **`assets/scene/layout.js`** — the scene's world-space placement + sizing.
2. **`components/work-showcase-editor.js`** — the editor's static layout preview.
3. **`work-showcase-geometry.php`** — the PHP mirror that emits the no-JS
   fallback's CSS custom properties.

`layout.js` and `work-showcase-geometry.php` implement the **same three
formulas** and must stay in lock-step (parity is verified numerically):

```
visibleHeightAt(z) = 2 * (cameraZ - z) * tan(fov / 2)
fitPlane(aspect)   -> h = min(maxH, maxW / aspect); w = h * aspect
projectSlot(slot)  -> x/y/w/h expressed as multiples of 1cqh
```

Everything is expressed in `1cqh` (1% of stage height) on a
`container-type: size` stage, so the fallback holds at any aspect without JS.

## Build

Runtime deps are pinned in the plugin `package.json`: `three@0.186.0`,
`gsap@^3.13`. Standard Eightshift build:

```bash
composer install
npm install
npm run build      # or: npm start  (watch)
```

The frontend-libs webpack auto-discovers `assets/index.js` and code-splits the
`import('./scene/Scene.js')` chunk, so three.js/gsap stay out of the initial
front-end bundle. Editor vs front-end are separate webpack configs, so there is
no chunk-name collision between the editor's live preview and the view module.

## Verification status

- ✅ PHP lints clean (`php -l`); JSON valid; ESM scene modules parse.
- ✅ JS ↔ PHP geometry parity confirmed numerically.
- ⚠️ **Not yet built in a running WordPress** — needs `npm install` +
  `npm run build`, then a smoke test in the editor and on the front end
  (WebGPU + `--forceWebGL`, mobile table at ≤768px, reduced-motion, no-JS).
- ⚠️ No cross-browser / Lighthouse / CLS pass yet.

## Open items inherited from the plan

- Real content + deploy (phase 5) not started.
- Plan §7 Q1–Q4 built to defaults; revisit tile count/positions with real art.
