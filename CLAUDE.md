# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

The guiding principle for all development: **maximally utilize the WPMVC framework, Bootstrap, and LazyBlocks** instead of writing things by hand. If WordPress core, the framework, Bootstrap, or LazyBlocks already solves a problem, use that solution — do not duplicate it.

## Commands

### Environment

```bash
# Start Docker (WordPress + MySQL + phpMyAdmin)
docker-compose up -d

# Stop Docker
docker-compose down
```

WordPress runs on `http://localhost:80`, phpMyAdmin on `http://localhost:8080`.

### PHP dependencies (project root)

```bash
composer install

# Lint with PHPCS
composer lint

# Lint theme against WordPress-VIP-Go standard
composer phpcs-theme
```

### Theme assets (`themes/theme/`)

```bash
npm install

# Watch for development
npm run dev

# Production build
npm run build
```

Webpack builds `assets/css/src/index.scss` and `assets/js/src/index.js` into `dist/css/main.css` and `dist/js/main.js`. SVG icons from `assets/icons/src/` are compiled into a sprite at `dist/icons/sprite.svg` (rendered with `theme\includes\Html::get_icon( $name, $width, $height )`).

## Architecture

A WordPress theme built on **WPMVC** — a custom MVC framework that wraps WordPress hooks into MVC abstractions, installed as the Composer package `devstudio-rs/wpmvc` (`themes/theme/vendor/devstudio-rs/wpmvc/`). Note: the `themes/theme/wpmvc/` directory is an older bundled copy — the autoloaded framework is the vendor package, so always reference that one. The theme lives in `themes/theme/`.

**Boot sequence:** `functions.php` → instantiates `Theme` (extends `wpmvc\App`) with config from `config/main.php` → `init()` registers the components (router, request, view, options, logger) → WordPress hooks take over.

**Namespaces:** theme classes use `theme\` (PSR-4 mapped to the theme root: `theme\models\` → `models/`, `theme\controllers\` → `controllers/`, `theme\includes\` → `includes/`). Framework classes use `wpmvc\`.

**Aliases** (`config/main.php`): `@root` → theme directory path, `@web` → theme directory URI, `@home` → `home_url()`. Resolve with `Theme::alias( '@root/...' )`.

**Assets** (`config/assets.php`): CSS/JS handles registered through the view component config — add new handles there, do not call `wp_enqueue_*` ad hoc in templates.

**Directory layout** (theme root):

- `models/` — Post/Taxonomy models (`theme\models\`)
- `controllers/` — route and meta box controllers (`theme\controllers\`)
- `views/` — plain PHP view templates, grouped per feature (`views/events/meta-info.php`)
- `blocks/` — LazyBlocks Gutenberg blocks, one folder per block
- `includes/` — standalone theme classes (`Block_Types`, `Html`, …)
- `assets/css/src/` — SCSS sources (`variables.scss`, `utilities.scss`, `custom.scss`, `index.scss`)
- `config/` — app config (`main.php`, `assets.php`, `params.php`)

## WPMVC rules

### Custom post types → always use models

Never call `register_post_type()` directly. Create a model in `models/` extending `wpmvc\models\Post_Model`:

- `public $post_type` defines the CPT slug.
- Every extra **public property is automatically post meta** — read on `one()`/`find_one()`, persisted on `save()`. No manual `get_post_meta`/`update_post_meta`.
- `registry()` returns the `register_post_type()` args (if empty, the CPT is not registered — useful for modeling built-in types).
- `registry_labels()`, `registry_supports()`, `registry_rewrite()` provide the labels/supports/rewrite args.
- `attribute_labels()` maps attribute names to admin-facing labels (used by `Form`).
- `meta_boxes()` declares the model's meta boxes (see below).
- `init()` runs after registration — for any extra hook wiring.
- `rules()` defines validation (`required`, `email`, `number`, or a callable), enforced by `save()`; errors via `get_errors()`/`add_error()`.
- `before_save()` / `after_save()` are the save hooks.

Register the model on the WordPress `init` action (e.g. in `functions.php`):

```php
add_action( 'init', array( \theme\models\Event::class, 'register' ) );
```

**Query API** — use it instead of raw `WP_Query`:

```php
Event::find_one( $id );
Event::find_all( $params );
Event::find()->published()->all();
Event::find()->where_taxonomy( Event_Category::class, array( 'slug' => 'music' ) )->all();
```

Helpers on instances: `get_id()`, `get_title()`, `get_content()`, `get_link()`, `get_thumbnail()`, `save()`, `delete()`, `load()`, `set_attribute(s)()`.

### Taxonomies → same pattern as post models

Extend `wpmvc\models\Taxonomy_Model`: `public $taxonomy` + extra public properties as term meta, `registry()`, `registry_labels()`, `registry_object_type()` (the post types it attaches to). Register on `init` the same way. Query with `find()`/`find_one()`/`all()`; term-meta admin fields via `add_meta_controller( Controller::class )` with a `wpmvc\web\Taxonomy_Controller` (`on_action`, `on_action_add`, `on_action_edit`, `on_save`).

### Meta boxes → controllers, not `add_meta_box()` calls

Declare them in the model's `meta_boxes()` method — `register()` wires them automatically:

```php
protected function meta_boxes() : array {
    return array(
        array( Event_Options_Controller::class, __( 'Options' ) ),
        // array( Controller::class, $title, $args ) — $title and $args are optional
    );
}
```

The controller extends `wpmvc\web\Meta_Box_Controller` and implements:

- `on_action( $model )` — render the box: `echo View::render( 'views/events/meta-info', array( 'model' => $model ) );`
- `on_save( $model )` — persist: `if ( $model->load( Theme::$app->request->post() ) ) { $model->save(); }`

### Forms → `wpmvc\helpers\Form`

Build admin/meta box fields with the Form helper bound to a model attribute — never hand-write inputs:

```php
echo Form::field( $model, 'city' )->select( Event::cities() );
echo Form::field( $model, 'date' )->input_text();
// also: ->textarea(), ->checkbox(), ->input( $type ), ->label( $text )
```

Field names follow the `Model[attribute]` convention that `$model->load()` expects.

### Views

`View::render( 'views/path/to/template', $params )` — params land in `$wp_query->query_vars` and the template is included via `get_template_part()`. Views are plain PHP under `views/`, one subfolder per feature, with a `@var` docblock declaring the passed variables.

### Routes and controllers

Custom endpoints go through the router, not template hacks or `admin-ajax` handlers:

```php
use wpmvc\web\Router;

Router::get( 'my-path', array( Site_Controller::class, 'action_index' ) );
Router::post( 'my-path/save', array( Site_Controller::class, 'action_save' ) );
Router::any( 'user/{action}/{id}', array( User_Controller::class, 'action_view' ) );
```

- `Router::get()` / `Router::post()` / `Router::any()` register a route for the given HTTP method (instance form: `Theme::$app->router->add_route( $path, $action, $method )`).
- `{param}` placeholders match a single path segment; their values are passed to the action as positional arguments in declaration order (`user/edit/5` → `action_view( 'edit', '5' )`).

Controllers extend `wpmvc\web\Controller`; action methods must be prefixed `action_`. Routes match on `template_redirect`. Use `Theme::$app->request->post()/get()` for input and `wp_send_json_success()/error()` (or `$model->to_response()`) for JSON responses.

### Theme options

Use the `options` component (`Theme::$app->options->get( $name, $default, $type )` / `->set()`), configured via the `wpmvc_options` filter — not raw `get_option()` scattered around.

## Frontend rules (Bootstrap)

Bootstrap is the styling foundation. In blocks, views, and templates:

- **Use Bootstrap classes to the maximum** (grid, spacing, typography, flex, buttons: `container`, `row`, `col-lg-6`, `mb-3`, `d-flex`, `align-items-center`, `btn btn-primary`, `img-fluid`, …). Only write custom CSS when no Bootstrap utility can express it.
- **Design tokens go in `assets/css/src/variables.scss`** — override Bootstrap defaults (colors, fonts, `$spacers`, `$font-sizes`…) and define new maps there, before the `~bootstrap` import in `index.scss`.
- **New utility classes go in `assets/css/src/utilities.scss`**, generated Bootstrap-style from a map in `variables.scss`. Existing example — the `w-max-*` utility built from the `$widths` map:

  ```scss
  // variables.scss
  $widths: (
    100: 100px,
  ) !default;

  // utilities.scss
  .w-max {
    @each $key, $value in $widths {
      &-#{$key} {
        max-width: $value;
      }
    }
    // + responsive -lg- variants
  }
  ```

  To extend, add entries to the map (and responsive variants following the same pattern) rather than writing one-off classes.
- One-off component styles that fit neither category belong in `custom.scss`.

## Gutenberg blocks (LazyBlocks)

Blocks are registered through the LazyBlocks plugin and auto-discovered by `theme\includes\Block_Types` from the `blocks/` directory.

**Structure** — one folder per block:

```
blocks/lazyblock-<name>/
├── config.php   # block + controls definition (overrides only)
└── block.php    # frontend template, receives $attributes
```

**`config.php` contains only overrides.** `Block_Types::normalize_block()` deep-merges it over a full default config, so never repeat defaults:

- `slug` is derived from the folder name (`lazyblock-image-text` → `lazyblock/image-text`).
- Control `name` is derived from the array key (`control_heading` → `heading`).
- Defaults: category `theme`, control type `text`, placement `inspector`, template output, standard `supports`/`ghostkit` config.

A typical block config is just title, icon, keywords, and controls:

```php
return array(
    'title'    => __( 'Image with Text', 'theme' ),
    'icon'     => 'dashicons-align-pull-left',
    'controls' => array(
        'control_image'   => array( 'type' => 'image', 'label' => __( 'Image', 'theme' ) ),
        'control_heading' => array( 'label' => __( 'Heading', 'theme' ) ),
        'control_content' => array( 'type' => 'rich_text', 'label' => __( 'Content', 'theme' ), 'placement' => 'content' ),
        'control_link'    => array( 'type' => 'url', 'label' => __( 'Link', 'theme' ) ),
    ),
);
```

**`block.php` conventions:**

- Root element classes: `block block-<name>` (e.g. `<div class="block block-image-text">`), then Bootstrap classes for the layout inside.
- **Images always via `wp_get_attachment_image()` with the attachment ID** — never a raw `<img src>` from the URL (this keeps srcset, lazy loading, and alt text from the media library):

  ```php
  <?php if ( ! empty( $attributes['image']['id'] ) ) : ?>
      <div class="col-lg-6">
          <?php echo wp_get_attachment_image( $attributes['image']['id'], 'full', false, array(
              'class' => 'img-fluid',
          ) ) ?>
      </div>
  <?php endif; ?>
  ```

- Guard every attribute with `! empty()` before output.
- Escape output: `esc_html()` for plain text, `esc_url()` for links, `wp_kses_post()` for rich text.

## Accessibility

Everything shipped must be accessible:

- Semantic HTML: real heading hierarchy (one `h1` per page, no skipped levels — in blocks prefer `h2`+), `<nav>`, `<button>` vs `<a>` used correctly (links navigate, buttons act).
- Images need meaningful `alt` text (`wp_get_attachment_image()` pulls it from the media library — another reason it is mandatory); decorative SVG icons get `aria-hidden="true"` and interactive icon-only controls get an `aria-label`.
- Links must have an accessible name — never an icon-only link without a label.
- Keyboard: everything interactive is focusable and usable without a mouse; do not remove focus outlines without a visible replacement.
- Maintain sufficient color contrast when defining colors in `variables.scss` (WCAG AA).

## Adding new functionality

1. **Look in `includes/` first.** If an existing class covers the domain (e.g. a `Security` class exists and the feature is security-related), extend that class with a new method.
2. **If the logic is substantial or a new domain, create a new class** in `includes/` (`theme\includes\`) following the existing pattern: a static `register()` that hooks into WordPress, called from `functions.php` (see `Block_Types`).
3. If it involves a CPT, taxonomy, meta box, form, or route — use the WPMVC abstractions above, never raw WordPress registration code.
4. No business logic in view/template files — views only render what they are given.
