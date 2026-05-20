# Category Label Block

Displays nested WooCommerce product category names (parent/child) with flexible ordering and formatting. Works on taxonomy archive pages and inside the product loop.

## Block Name

`eightshift-boilerplate/category-label`

## Adding the Block

1. In the WordPress editor, click the **+** block inserter
2. Search for **"Category Label"**
3. Insert the block into your template or page

## Settings (Sidebar Inspector)

### Source

| Option | Description |
|--------|-------------|
| **Archive (Current Term)** | Reads the current queried term on a `product_cat` taxonomy archive page. Uses the term and its immediate parent. |
| **Product Loop** | Reads categories from the current `global $product` in a WooCommerce product loop (e.g. inside `woocommerce/product-template`). |

### Filter by Parent (Product Loop only)

When Source is set to "Product Loop", optionally restrict which parent category tree to read from:

- None (auto-detect deepest category)
- Active Ingredients
- Nutrition Facts
- Ingredients
- Mood
- Product Type
- Cana Facts

### Name Order

Controls which parts of the category hierarchy to display and in what order:

| Format | Example Output |
|--------|----------------|
| **Child Parent** | 5MG CBD |
| **Parent Child** | CBD 5MG |
| **Parent Only** | CBD |
| **Child Only** | 5MG |

### Separator

The character(s) placed between parent and child names. Default is a single space. You can use ` / `, ` - `, or any string.

### Text Transform

| Option | Example |
|--------|---------|
| **Uppercase** | 5MG CBD |
| **Lowercase** | 5mg cbd |
| **Capitalize** | 5mg Cbd |
| **None** | As stored in WordPress |

## Usage in Block Templates

### On a taxonomy archive page (e.g. `taxonomy-product_cat.html`)

```html
<!-- wp:eightshift-boilerplate/category-label {"categoryLabelSource":{"label":"Archive (Current Term)","value":"archive","restName":"archive"},"categoryLabelFormat":{"label":"Child Parent","value":"child-parent","restName":"child-parent"},"categoryLabelTransform":{"label":"Uppercase","value":"uppercase","restName":"uppercase"}} /-->
```

### Inside a WooCommerce product loop

```html
<!-- wp:eightshift-boilerplate/category-label {"categoryLabelSource":{"label":"Product Loop","value":"product","restName":"product"},"categoryLabelParentSlug":{"label":"Active Ingredients","value":"active-ingredients","restName":"active-ingredients"},"categoryLabelFormat":{"label":"Child Parent","value":"child-parent","restName":"child-parent"}} /-->
```

### Show only the parent name

```html
<!-- wp:eightshift-boilerplate/category-label {"categoryLabelFormat":{"label":"Parent Only","value":"parent-only","restName":"parent-only"}} /-->
```

## How It Works

- **Archive mode**: Calls `get_queried_object()` to get the current term. If it has a parent, the parent becomes "Parent" and the current term becomes "Child". If the term is top-level, it is treated as "Parent" with no child.
- **Product loop mode**: Reads all `product_cat` terms assigned to the product. If a parent slug is specified, it finds that parent and its direct child. Otherwise it auto-detects the deepest category in the hierarchy.

## File Structure

```
category-label/
├── manifest.json                  # Block attributes and allowed options
├── category-label.php             # Server-side PHP render
├── category-label-block.js        # React block entry (editor)
├── category-label-style.scss      # Frontend styles
├── category-label-editor.scss     # Editor-only styles
├── README.md                      # This file
└── components/
    ├── category-label-editor.js   # ServerSideRender preview
    └── category-label-options.js  # Inspector panel controls
```
