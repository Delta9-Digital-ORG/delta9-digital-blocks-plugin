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

$productCategoryListName = (Helpers::checkAttr('productCategoryListName', $attributes, $manifest))['value'];
$productCategoryListFormat = (Helpers::checkAttr('productCategoryListFormat', $attributes, $manifest))['value'];

global $product;

if(isset($product)) {
	$productID = $product->get_id();
} else {
	$productID = 584;
}

if($productCategoryListFormat == 'Stacked') {
	$blockClass .= " product-category-format-stacked";
} elseif($productCategoryListFormat == 'Reverse Order') {
	$blockClass .= " product-category-format-reverse-order";
}

$categoriesArray = array();
$productParentCats = array();
$productChildCats = array();
$productCategories = get_the_terms( $productID, 'product_cat' );

foreach($productCategories as $prodCat) {
	if($prodCat->parent == 0) {
		$productParentCats[] = $prodCat->term_id;
	} else {
		$productChildCats[] = $prodCat->term_id;
	}
}

$args = array(
    'hide_empty' => false,
    'parent'   => 0
);
$product_cat = wp_get_post_terms($productID, 'product_cat', $args);

foreach ($product_cat as $parent_product_cat) {
	if(in_array($parent_product_cat->term_id, $productParentCats) && $parent_product_cat->name == $productCategoryListName) {
		$categoriesArray[$parent_product_cat->name]['object'] = $parent_product_cat;
		
		$child_args = array(
		            'hide_empty' => false,
		            'parent'   => $parent_product_cat->term_id
		        );
		$child_product_cats = wp_get_post_terms($productID, 'product_cat', $child_args);
		
		// Subcategories
		foreach ($child_product_cats as $child_product_cat) {
			if(in_array($child_product_cat->term_id, $productChildCats)) {
				$categoriesArray[$parent_product_cat->name]['children'][$child_product_cat->term_id]['object'] = $child_product_cat;
				
				$subcat_child_args = array(
		            'hide_empty' => false,
		            'parent'   => $child_product_cat->term_id
		        );
			    $subcat_product_cats = wp_get_post_terms($productID, 'product_cat', $subcat_child_args);
				
				foreach($subcat_product_cats as $subcat) {
					if($subcat->parent == $child_product_cat->term_id && $subcat->count != 0) {
						$categoriesArray[$parent_product_cat->name]['children'][$child_product_cat->term_id]['children'][] = $subcat;
					}
				}
			}
		}
	}
}

echo '<div class="' . $blockClass . ' product-category-list-custom-container">';

if(!empty($categoriesArray)) {
	foreach($categoriesArray as $parentCategory) {
		if(!empty($parentCategory['children']) && count($parentCategory['children']) > 0) {
			foreach($parentCategory['children'] as $catChild) {
				$catChildObj = $catChild['object'];
				
				if($productCategoryListFormat == 'List') {
					echo '<div class="product-category-list-custom-list">';
					echo '<span class="product-category-list-custom-category">' . $catChildObj->name . '</span>';
					
					if(isset($catChild['children'])) {
						echo '<ul>';
	
						foreach($catChild['children'] as $subCat) {
							echo '<li class="product-category-list-custom-subcategory">' . $subCat->name . '</li>';
						}
	
						echo "</ul>";
					}
					echo '</div>';
					
				} elseif($productCategoryListFormat == 'Nutrition Facts') {
					echo '<div class="product-category-list-custom-nutrition-facts">';
					echo '<span class="product-category-list-custom-category">' . $catChildObj->name . '</span>';
					
					foreach($catChild['children'] as $subCat) {
						echo '<span class="product-category-list-custom-subcategory">' . $subCat->name . '</span>';
					}
					
					echo '</div>';
				}
			}
		}
	}
}
echo "</div>";

?>
