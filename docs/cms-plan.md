# WODI CMS — Media Library + Page Builder

> Planning document. Nothing here is built yet.

## Context

The 8 public Blade pages are 100% hardcoded: ~20 inline `@php $x = [...]` arrays across
`home/about/admissions/resources/gallery/forms/faq/contact.blade.php`, images referenced by
convention (`"images/about/teacher-" . ($i+1) . ".png"`), and every form/filter/carousel is dead
(`action="#"`). Nothing is editable without a deploy.

We want the ergonomics of [coders-tm/laravel-page-builder](https://github.com/coders-tm/laravel-page-builder)
— section tree + iframe live preview + field panel + device toggles — **without** the two things that
made us reject Statamic:

1. **No flat files.** coders-tm stores pages as JSON *files*. We store everything in MySQL.
2. **No generic blob table for domain data.** Programs, classes, FAQs, team get **real tables**.

### Two tiers (the anti-Statamic bit)

- **Tier 1 — domain tables.** `programs`, `program_categories`, `classes`, `faqs`, `team_members`,
  `testimonials`, `gallery_items`. Real, relational, queryable.
- **Tier 2 — the builder.** A page is an ordered list of sections. A section holds *literal copy*
  (heading, body, image) **or binds to a domain model** — a "Programs Grid" stores *which category,
  how many, what layout*, not the programs themselves.

**The builder owns layout and the query. It never owns the data.**

### Portability: the contract is the API, not a package

Two self-contained React components, each talking to a documented HTTP interface, each backed by
**exactly one Laravel controller**. To reuse either elsewhere: copy the folder, write a new API
adapter, point it at any backend. No composer package, no publish step, no framework coupling.

```
resources/js/cms/            <- the copyable folder
  media/
    types.ts                 MediaItem, MediaFolder, MediaQuery
    api.ts                   MediaApi interface + createHttpMediaApi(baseUrl)
    components/              media-library, media-picker, media-dropzone, ...
  builder/
    types.ts                 PageDoc, SectionInstance, FieldSchema
    api.ts                   BuilderApi interface + createHttpBuilderApi(baseUrl)
    components/              page-builder (3-pane shell), section-tree,
                             preview-frame, field-panel, fields/*
```

Components take their API as a prop — nothing is hardwired:

```tsx
<MediaLibrary api={createHttpMediaApi('/admin/media')} />
<PageBuilder  api={createHttpBuilderApi('/admin/builder')} pageId={12} />
```

### Dependency surface — deliberately tiny

`resources/js/cms/` may import **only**:

| | |
|---|---|
| `react` | |
| `@headlessui/react` | the hard, accessibility-critical primitives — see below |
| `lucide-react` | icons (already in the project) |
| a local `cn()` | ~3 lines, vendored inside the folder — not imported from `@/lib/utils` |

**No shadcn. No Radix. No Inertia. No Wayfinder. No `@/` imports at all.** The admin's existing
shadcn components (`resources/js/components/ui/*`) stay where they are and keep serving the rest of
the admin — the CMS folder simply doesn't reach for them. That's what makes the folder genuinely
liftable: drop it in any React project, `npm i @headlessui/react lucide-react`, write an API adapter,
done.

**Use Headless UI for** (fiddly focus-management / ARIA / keyboard work we should not reimplement):

- `Dialog` — the media-picker modal (focus trap, scroll lock, escape handling)
- `Combobox` — media search, and the builder's `relation` field
- `Listbox` — the builder's `select` field
- `Menu` — per-item action menus
- `Popover`, `Transition`, `Tab`, `Switch`

**Hand-build** (small enough that a dependency isn't worth it): button, input, textarea, label,
filter chip, checkbox (styled native input), progress bar, card, skeleton, table (plain `<table>`),
scroll container (plain `overflow-auto`).

Headless UI v2 supports React 19 and comes from the Tailwind team, so it composes cleanly with the
Tailwind v4 setup already here.

---

## Milestone 1 — Media (build this first)

### No folders — the library is flat

WordPress has no folders, and it's the reference we're working from. A flat table plus **search +
type filter** does the same job without a nested tree, materialised paths, per-parent unique slugs,
move-between-folders, or a tree sidebar. Files are found by *searching*, not by *navigating*.

If grouping is ever genuinely needed, the cheap answer is a flat `tags` string column filtered with
a chip row — not a hierarchy. Not building it now.

### The API contract

Four methods. That's the whole surface.

```ts
export interface MediaApi {
  list(q: MediaQuery): Promise<{ data: MediaItem[]; nextCursor: string | null }>
  upload(files: File[], opts?: { onProgress?: (p: number) => void }): Promise<MediaItem[]>
  update(id: number, patch: Partial<Pick<MediaItem, 'title' | 'alt' | 'description'>>): Promise<MediaItem>
  destroy(id: number): Promise<void>            // rejects with MediaInUseError + usages
}

export interface MediaQuery {
  q?: string
  kind?: MediaItem['kind']
  cursor?: string
}

export interface MediaItem {
  id: number
  url: string                 // the original file — no conversions/thumbnails
  kind: 'image' | 'video' | 'audio' | 'document' | 'archive' | 'other'
  mimeType: string
  originalName: string
  title: string | null        // searchable
  alt: string | null          // searchable + used as the <img alt> on the site
  description: string | null  // searchable
  size: number
  width: number | null
  height: number | null
  createdAt: string
}
```

**Deliberately thin** (per the simplification): no `checksum` → no dedupe; no `conversions` → the
card shows the original `url`, no thumbnail pipeline, no image-processing package. The only
metadata is the three **searchable** text fields (`title`, `alt`, `description`). `width`/`height`
come free from native `getimagesize()` and just drive the card's aspect ratio.

`createHttpMediaApi(baseUrl)` is the default `fetch` implementation. **That function is the only
Laravel-aware code in the whole folder** — swapping backends means replacing it.

### One controller

`app/Http/Controllers/MediaController.php`

| method | route | purpose |
|---|---|---|
| `index` | `GET /admin/media` | `?q= &kind= &cursor=` |
| `store` | `POST /admin/media` | multipart, multi-file |
| `update` | `PATCH /admin/media/{media}` | title / alt / description |
| `destroy` | `DELETE /admin/media/{media}` | 409 + usage list if in use |

Four methods, one controller. All JSON (**not** Inertia — the picker opens in a dialog and must not
navigate). Plus one Inertia route `/admin/media` → `pages/media/index.tsx` for the full-page library
screen, which just mounts `<MediaLibrary/>`.

### Tables

Two tables. **`media`** — a lean metadata row:

| column | notes |
|---|---|
| `disk`, `path`, `filename`, `original_name`, `extension`, `mime_type` | |
| `kind` | `image \| video \| audio \| document \| archive \| other` — indexed, drives the filter chips |
| `size` | bytes |
| `width`, `height` | nullable — from native `getimagesize()`, drives card aspect ratio |
| `title`, `alt`, `description` | the editable, **searchable** metadata |
| `uploaded_by` | FK users, nullable |
| | timestamps + softDeletes |

**No `checksum`, no `conversions`, no `meta` JSON.** Index `(title, alt, original_name)` +
`description` for search; index `kind`.

**`mediables`** — polymorphic pivot: `media_id`, `mediable_type`, `mediable_id`, `collection`,
`position`. Makes "where is this image used?" answerable and lets us **block deletion of an in-use
file** — the thing a raw file-upload can never do.

### PHP

- `App\Models\Media` — house conventions: `#[Fillable]` + `@property` docblocks
  (see `app/Models/User.php`) so Larastan stays green.
- `HasMedia` trait — any model gets `->media()`, `->attachMedia($id, 'hero')`, `->getMedia('hero')`.
- `MediaUploader` service — validate → store → probe dimensions with native `getimagesize()`. **No
  hashing, no conversions, no queued jobs.** Just move the file and write the row.
- `MediaPolicy` + a `cms` gate on `users.user_type`. **This is the app's first authorization layer** —
  `user_type` exists in the migration but is checked nowhere today.

### React components

- **`<MediaLibrary api mode/>`** — search box, kind-filter chips, grid, infinite scroll, details panel
  (title/alt/description edited inline), bulk select, delete-with-usage-check, drag-and-drop upload
  over the grid. `mode: 'manage' | 'select'`. No sidebar, no tree.

  **Mounts anywhere, unchanged.** The exact same component renders as a full dashboard page *or*
  inside a modal — it makes no layout assumptions about its host. The full-page screen
  (`pages/media/index.tsx`) and the picker's dialog both mount `<MediaLibrary/>`; there is one
  implementation, two hosts.

  **Container-responsive, not page-responsive.** The card grid keys off its *own width*, not the
  viewport. Tailwind v4 ships container queries natively, so the grid wrapper is `@container` and the
  cards use `@[...]` variants (e.g. `grid-cols-2 @sm:grid-cols-4 @lg:grid-cols-6`). Result: the grid
  looks right whether it's full-bleed on the page or squeezed into a narrow modal — no `md:` viewport
  breakpoints anywhere in this folder.

- **`<MediaPicker api value multiple kind onChange/>`** — **the droppable one.** Renders a thumbnail
  or empty slot; click opens a Dialog hosting `<MediaLibrary mode="select"/>`. One library, two
  surfaces — not a reimplementation.
- **`<MediaDropzone/>`** — bare upload target.

### Reuse from `medplus-cc/backend/resources/js` (ported, not copied verbatim)

That project's `Modal`, `SearchBox`, `FilePicker` and `SimpleImagePicker` already implement exactly
the drag-drop-upload + Headless-UI-modal UX we want, and they're pure Headless UI + Tailwind + native
inputs (**no MUI**). They become the *basis* for our media components, with three couplings stripped
to fit this app:

| medplus dependency | replaced with |
|---|---|
| Bootstrap Icons (`<i class="bi bi-*">`) | `lucide-react` (this app's icon set) |
| global `route()` (Ziggy) | the injected `MediaApi` — no route helper inside the folder |
| `@/hooks/notificationContext` | a small `onError` callback prop (host wires it to the app's toast) |
| custom classes `btn-pink`, `shadow-s1`, `ring-primary-600` | wodi-admin utility classes |

Net: we keep the interaction design that's already proven in your other app, and shed the parts that
would couple this folder to a specific project.

### New dependencies

- composer: **none** — no image-processing package (no conversions), dimensions via native
  `getimagesize()`.
- npm: `@headlessui/react` — **the only new UI dependency.** No shadcn/Radix, no image lib.
- `php artisan storage:link` — **the symlink does not exist**; uploads cannot be served without it.

### Build order

1. **Dashboard menu entry + `/admin/media` page** — get the nav link and an empty library screen on
   screen first, behind the `cms` gate.
2. Migration + `Media` model + `MediaController` (`index`/`store`).
3. `<MediaLibrary/>` (grid, container-query cards, search, drag-drop upload) rendering on the page.
4. Inline edit (`update`) + delete-with-usage-check (`destroy`) + the `mediables` pivot.
5. `<MediaPicker/>` wrapping the same library in a modal — proves the mount-anywhere claim.

---

## Milestone 2 — Page builder

### The JSON the builder consumes

Two documents. **This is the contract** — any backend that serves these shapes drives the component.

```ts
// 1. The schema: what sections exist and what fields each has. Draws the right-hand panel.
type FieldSchema =
  | { id: string; type: 'text' | 'textarea' | 'richtext' | 'number' | 'url' | 'color'; label: string; default?: unknown }
  | { id: string; type: 'select'; label: string; options: { value: string; label: string }[] }
  | { id: string; type: 'media'; label: string; kind?: MediaItem['kind']; multiple?: boolean }
  | { id: string; type: 'repeater'; label: string; fields: FieldSchema[] }
  | { id: string; type: 'relation'; label: string; source: string }   // binding sections

interface SectionSchema {
  key: string
  name: string
  group: string
  fields: FieldSchema[]
}

// 2. The page document.
interface PageDoc {
  id: number
  slug: string
  title: string
  status: 'draft' | 'published'
  meta: { title?: string; description?: string; ogMediaId?: number }
  sections: SectionInstance[]
}

interface SectionInstance {
  id: number
  type: string
  position: number
  isVisible: boolean
  settings: Record<string, unknown>
  children?: SectionInstance[]
}
```

```ts
export interface BuilderApi {
  schema(): Promise<SectionSchema[]>
  getPage(id: number): Promise<PageDoc>
  savePage(id: number, doc: PageDoc): Promise<PageDoc>
  renderSection(type: string, settings: Record<string, unknown>): Promise<string>  // HTML for preview
  options(source: string): Promise<{ value: string; label: string }[]>             // for relation fields
}
```

### One controller

`app/Http/Controllers/PageBuilderController.php` — `schema`, `show`, `save`, `renderSection`,
`options`. Plus an Inertia route `/admin/pages/{page}/edit` mounting `<PageBuilder/>`.

### Where the schema comes from (PHP side — swappable, not Blade-dependent)

One small PHP class per section, serialising to the `SectionSchema` JSON above:

```php
class OurStorySection extends Section
{
    public static string $key  = 'our_story';
    public static string $name = 'Our Story';

    public function fields(): array
    {
        return [
            Text::make('eyebrow'),
            Text::make('title')->required(),
            MediaField::make('image')->images(),
            RichText::make('body'),
            Repeater::make('stats')->fields([Text::make('value'), Text::make('label')]),
            Relation::make('category')->source('program_categories'),
        ];
    }
}
```

**Rendering is a separate concern.** `renderSection` returns HTML via Blade here; a non-Blade app
serves the same `PageDoc` from `GET /api/cms/pages/{slug}` (sections + hydrated values + resolved
media URLs) and renders it itself. The React builder doesn't care which.

### Tables

- `pages` — `slug` (unique), `title`, `status`, `published_at`, `meta_*`, `og_media_id`, softDeletes
- `page_sections` — `page_id`, `parent_id`, `type`, `position`, `is_visible`, `settings` JSON
- `page_revisions` — `snapshot` JSON, `user_id` — powers the "Restore section" button

On save, `media` ids inside `settings` are **mirrored into `mediables`** — media stays relational
even though settings are JSON.

### Editor

Three panes matching the reference: section tree (left, drag-reorder), iframe preview (centre, device
toggles), field panel (right, drawn from the schema JSON).

Reordering the section list is a flat vertical sort — **hand-built on native HTML5 drag events**, no
library. If nested block drag-and-drop later proves genuinely painful, `@dnd-kit/*` is the fallback:
it's headless (behaviour only, ships no components or styles), so it doesn't violate the no-UI-library
rule — but we don't reach for it until the hand-built version actually falls over.

The iframe loads the **real site URL** with `?editor=1`. Because it's the real page it loads
`site.css` — the `wodi-*` tokens work and the admin's `app.css` stays isolated. **The site/admin CSS
separation survives untouched.** Field change → `renderSection` → node swapped via `postMessage`.

### Two constraints to design around

1. **Tailwind cannot scan the database.** A colour/variant field must map through a PHP `match()` in
   the template so the literal class appears in source. Never store raw Tailwind classes in `settings`.
2. **Some values in today's arrays are design, not content.** `$heroCards`' `w-[191%]` /
   `-translate-x-[48.5%]` came from measuring PNG alpha bboxes — they stay in the template and are
   **not** editable.

---

## Later milestones

3. Domain tables + binding sections
4. Convert the 8 pages to sections
5. Wire what's dead: contact form, newsletter, resources search + filter chips, carousel arrows,
   "Download Form" / "Enrol Class" buttons (`contact_messages`, `newsletter_subscribers`)

---

## Verification (milestone 1)

- `php artisan migrate` clean; `php artisan storage:link` created.
- Pest: upload creates one `media` row; `DELETE` on an in-use file → 409 + usage list; search matches
  against `title`/`alt`/`description`/`original_name`; kind filter returns the right set; a non-`cms`
  `user_type` → 403.
- Browser: visit `/admin/media`, drag in an image + a PDF, confirm they appear, edit alt/description,
  search for a file by its description, try deleting one that's in use. **Shrink the window / open the
  library in the picker modal and confirm the card grid reflows off the container, not the viewport.**
- `composer ci:check` (Pint + Larastan + tsc + Pest) green.
- **Portability check:** `grep -rE "from '@/|shadcn|@radix-ui|@inertiajs" resources/js/cms/` returns
  **nothing**. The folder's only imports are `react`, `@headlessui/react`, `lucide-react`, and its
  own local files.
