<?php

/**
 * Template for the Tax Query Menu Block view.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

//$globalManifest = Helpers::getManifest(dirname(__DIR__, 2));
$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$unique = Helpers::getUnique();

$taxQueryMenuItemContent = Helpers::checkAttr('taxQueryMenuItemContent', $attributes, $manifest);

$taxQueryMenuItemTaxonomy = Helpers::checkAttr('taxQueryMenuItemTaxonomy', $attributes, $manifest);
$taxQueryMenuItemManualTerms = Helpers::checkAttr('taxQueryMenuItemManualTerms', $attributes, $manifest);

$taxQueryMenuItemServerSideRender = Helpers::checkAttr('taxQueryMenuItemServerSideRender', $attributes, $manifest);

$taxonomyName = $taxQueryMenuItemTaxonomy['value'];
$taxQueryMenuItemButtonVariant = Helpers::checkAttr('taxQueryMenuItemButtonVariant', $attributes, $manifest);

$taxQueryMenuItemButtonAlign = Helpers::checkAttr('taxQueryMenuItemButtonAlign', $attributes, $manifest);

$taxQueryMenuItemShowCount = Helpers::checkAttr('taxQueryMenuItemShowCount', $attributes, $manifest);

$taxQueryMenuItemPickTaxonomyAll = Helpers::checkAttr('taxQueryMenuItemPickTaxonomyAll', $attributes, $manifest);

$taxQueryMenuItemPickTaxonomyOther = Helpers::checkAttr('taxQueryMenuItemPickTaxonomyOther', $attributes, $manifest);

$modalButtonType = 'tax-query';
$modalButtonId = 'tax-query-button';
$componentClass = 'button tax-query-menu-item-position__' . $taxQueryMenuItemButtonAlign .' tax-query-menu-item__' . $taxQueryMenuItemButtonVariant ;
$componentJsClass = 'js-tax-queryl-toggle';


if (!$taxonomyName || !$taxQueryMenuItemManualTerms && !$taxQueryMenuItemPickTaxonomyAll) {
	return;
}
?>

<div class="<?php echo esc_attr($blockClass); ?>" data-id="<?php echo esc_attr($unique); ?>">
	<?php
	$taxQueryMenuItemCurrentQuieriedObject = get_queried_object();
	$taxQueryMenuItemTermQuieriedActiveItem = '';

	if($taxQueryMenuItemCurrentQuieriedObject) {
		if($taxQueryMenuItemCurrentQuieriedObject->term_id){
			if(!$taxQueryMenuItemCurrentQuieriedObject->parent){
				$taxQueryMenuItemTermQuieriedActiveItem = 'all';
			} else {
				$taxQueryMenuItemTermQuieriedActiveItem = $taxQueryMenuItemCurrentQuieriedObject->slug;
			}

		} else if ($taxQueryMenuItemCurrentQuieriedObject->post_title){
			$taxQueryMenuItemTermQuieriedActiveItem = 'all';

		}
		
	}



	if ($taxQueryMenuItemPickTaxonomyAll){
		$taxQueryMenuItemManualTermLabel = 'View all';
		$taxQueryMenuItemManualTermSlug = 'all';
		$taxQueryMenuItemManualTermIcon = $taxQueryMenuItemButtonVariant === 'outline' ? 'term-all-home-icon' :'term-all-icon';
		$taxQueryMenuItemManualTermCount = 0;
		$taxQueryMenuItemManualTermsArray = [];
		$taxQueryMenuItemManualTermCountArray = [];
		if ($taxQueryMenuItemManualTerms){
			$taxQueryMenuItemManualTermId = $taxQueryMenuItemManualTerms[0]['id'];
			$taxQueryMenuItemManualTermsArray =  get_terms( array(
				'taxonomy'   => $taxonomyName,
				'hide_empty' => false,
				'parent' => $taxQueryMenuItemManualTermId,
			) );
		} else {
			
			$taxQueryMenuItemManualTermsArray =  get_terms( array(
				'taxonomy'   => $taxonomyName,
				'hide_empty' => false,
			) );
		}

		foreach ($taxQueryMenuItemManualTermsArray as $termObject) {
			$taxQueryMenuItemManualTermCountArray[] = $termObject->count;
		}

		$taxQueryMenuItemManualTermCount = array_sum($taxQueryMenuItemManualTermCountArray);


	} else {
		
		$taxQueryMenuItemManualTermId = $taxQueryMenuItemManualTerms[0]['id'];
		
		$taxQueryMenuItemManualTermObj = get_term($taxQueryMenuItemManualTermId);
		$taxQueryMenuItemManualTermSlug = $taxQueryMenuItemManualTermObj->slug;
		$taxQueryMenuItemManualTermId = $taxQueryMenuItemManualTermObj->term_id;
		$taxQueryMenuItemManualTermLabel = $taxQueryMenuItemManualTermObj->name;
		$taxQueryMenuItemManualTermCount = $taxQueryMenuItemManualTermObj->count;
		$taxQueryMenuItemTaxonomySlug = $taxQueryMenuItemManualTermObj->taxonomy;
		$taxQueryMenuItemManualTermIcon = 'term-' . $taxQueryMenuItemTaxonomySlug . '-' . $taxQueryMenuItemManualTermSlug . '-icon';


	
		if ($taxQueryMenuItemPickTaxonomyOther){
			$taxQueryMenuItemManualTermLabel = 'Other';
		
		}

	}
	if ($taxQueryMenuItemShowCount) {
		$taxQueryMenuItemManualTermLabel = $taxQueryMenuItemManualTermLabel . '<span style="color:var(--wp--preset--color--tertiary)"> (' . $taxQueryMenuItemManualTermCount . ')</span>';
	}
	

	$componentJsClass = Helpers::classnames([
		$componentClass,
		$taxQueryMenuItemTermQuieriedActiveItem === $taxQueryMenuItemManualTermSlug ? 'active' : '',
	]);

	

	// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	echo Helpers::outputCssVariables($attributes, $manifest, $unique),
	Helpers::render('button', Helpers::props('button', $attributes, [
	'blockClass' => $componentClass,
	'additionalClass' => $componentJsClass,
	'buttonContent' => $taxQueryMenuItemManualTermLabel,
	'buttonIconName' => $taxQueryMenuItemManualTermIcon,
		'buttonAttrs' => [
			'data-modal-button-type' => $modalButtonType,
			'data-modal-button-id' => $modalButtonId,
			'data-js-modal-toggle-open' => $modalButtonId,
			'aria-hidden' => 'false',
			'style' => 'flex-direction: column;',
		]
	]), '', true);

	?>
</div>
