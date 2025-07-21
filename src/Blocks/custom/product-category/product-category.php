<?php

/**
 * Template for the Product Category Block.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$unique = Helpers::getUnique();

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$productCategoryName = (Helpers::checkAttr('productCategoryName', $attributes, $manifest))['value'];
$productCategoryBorder = Helpers::checkAttr('productCategoryBorder', $attributes, $manifest);
$productCategoryBorderThick = Helpers::checkAttr('productCategoryBorderThick', $attributes, $manifest);
$productCategoryFormat = (Helpers::checkAttr('productCategoryFormat', $attributes, $manifest))['value'];
$productCategoryDisplayGrandchildren = Helpers::checkAttr('productCategoryDisplayGrandchildren', $attributes, $manifest);

if($productCategoryBorder) {
	$blockClass .= " product-category-format-border";
}

if($productCategoryBorderThick) {
	$blockClass .= " product-category-format-border-thick";
}

if($productCategoryFormat == 'Stacked') {
	$blockClass .= " product-category-format-stacked";
} elseif($productCategoryFormat == 'Reverse Order') {
	$blockClass .= " product-category-format-reverse-order";
}

global $product;

$categoriesArray = array();
$productParentCats = array();
$productChildCats = array();
$productCategories = get_the_terms( $product->ID, 'product_cat' );

foreach($productCategories as $prodCat) {
	if($prodCat->parent == 0) {
		$productParentCats[] = $prodCat->term_id;
	} else {
		$productChildCats[] = $prodCat->term_id;
	}
}

$args = array(
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
    'parent'   => 0
);
$product_cat = get_terms( $args );

foreach ($product_cat as $parent_product_cat) {
	if(in_array($parent_product_cat->term_id, $productParentCats) && $parent_product_cat->name == $productCategoryName) {
		$categoriesArray[$parent_product_cat->name]['object'] = $parent_product_cat;
		
		$child_args = array(
		            'taxonomy' => 'product_cat',
		            'hide_empty' => false,
		            'parent'   => $parent_product_cat->term_id
		        );
		$child_product_cats = get_terms( $child_args );
		
		// Subcategories
		foreach ($child_product_cats as $child_product_cat) {
			if(in_array($child_product_cat->term_id, $productChildCats)) {
				$categoriesArray[$parent_product_cat->name]['children'][$child_product_cat->term_id]['object'] = $child_product_cat;
				
				$subcat_child_args = array(
		            'taxonomy' => 'product_cat',
		            'hide_empty' => false,
		            'parent'   => $child_product_cat->term_id
		        );
			    $subcat_product_cats = get_terms( $subcat_child_args );
				
				foreach($subcat_product_cats as $subcat) {
					if($subcat->parent == $child_product_cat->term_id && $subcat->count != 0) {
						$categoriesArray[$parent_product_cat->name]['children'][$child_product_cat->term_id]['children'][] = $subcat;
					}
				}
			}
		}
	}
}

echo '<div class="' . $blockClass . ' product-categories-container">';

if(!empty($categoriesArray)) {
	foreach($categoriesArray as $parentCategory) {
		if(count($parentCategory['children']) > 0) {
			foreach($parentCategory['children'] as $catChild) {
				$catChildObj = $catChild['object'];
				
				if($productCategoryName == 'Active Ingredient Label') {
					if(strlen($catChildObj->name) < 12) {
						$spacing = "&nbsp;&nbsp;";
						$class = "product-category-active-ingredient-label-small";
					} else {
						$spacing = "&nbsp;&nbsp;";
						$class = "product-category-active-ingredient-label-large";
					}
				?>
					<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
						<circle cx="50" cy="50" r="50"/>
						<path id="circlePath" fill="none" stroke-width="none" stroke="none" d="
							M 15, 50
							a 5,5 0 1,1 70,0
							a 5,5 0 1,1 -70,0
						" />
						<text class="<?php echo $class; ?>">
							<textPath id="textPath" href="#circlePath">
								<?php echo $catChildObj->name . $spacing . $catChildObj->name; ?>
					    	</textPath>
						</text>
						<?php
						if(isset($catChild['children']) && $productCategoryDisplayGrandchildren) {
							foreach($catChild['children'] as $subCat) {
								$centeredText = explode(' ', $subCat->name);
								?>
								<text class="product-category-active-ingredient-label-inner-text" x="50%" y="42%">
									<?php echo $centeredText[0]; ?>
								</text>
								<text class="product-category-active-ingredient-label-inner-text" x="50%" y="58%">
									<?php echo $centeredText[1]; ?>
								</text>
								<?php
							}
						}
					?>
					</svg>
				<?php
				
				} else {
					echo '<div class="product-category-container">';
						echo '<span class="product-category-name">' . $catChildObj->name . '</span>';
						
						if(isset($catChild['children']) && $productCategoryDisplayGrandchildren) {
							foreach($catChild['children'] as $subCat) {
								echo '<span class="product-category-subcategory">' . $subCat->name . '</span>';
							}
						}
						
					echo "</div>";
				}
			}
		}
	}
}
echo "</div>";

?>
