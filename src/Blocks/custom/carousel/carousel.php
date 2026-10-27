<?php

/**
 * Template for the Carousel Block.
 *
 * @package Delta9DigitalBlocksPlugin
 */

use Delta9DigitalBlocksPluginVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';
$blockJsClass = $manifest['blockJsClass'] ?? $attributes['blockJsClass'] ?? '';

$carouselIsLoop = Helpers::checkAttr('carouselIsLoop', $attributes, $manifest);
$carouselShowItems = Helpers::checkAttr('carouselShowItems', $attributes, $manifest);
$carouselShowPrevNext = Helpers::checkAttr('carouselShowPrevNext', $attributes, $manifest);
$carouselShowPagination = Helpers::checkAttr('carouselShowPagination', $attributes, $manifest);
$carouselIsAutoplay = Helpers::checkAttr('carouselIsAutoplay', $attributes, $manifest);
$carouselEffect = Helpers::checkAttr('carouselEffect', $attributes, $manifest);
$carouselCenteredSlides = Helpers::checkAttr('carouselCenteredSlides', $attributes, $manifest);
$carouselSpaceBetween = Helpers::checkAttr('carouselSpaceBetween', $attributes, $manifest);
$carouselMobileItems = Helpers::checkAttr('carouselMobileItems', $attributes, $manifest);
$carouselAutoplayDelay = Helpers::checkAttr('carouselAutoplayDelay', $attributes, $manifest);
$carouselFill = Helpers::checkAttr('carouselFill', $attributes, $manifest);

$wrapperClass = Helpers::classnames([
	'block-carousel-wrapper',
	'block-carousel-wrapper_' . ($carouselEffect ?: 'default'),
	$carouselFill ? 'block-carousel-wrapper--fill' : '',
]);

$carouselClass = Helpers::classnames([
	$blockClass,
	$blockJsClass,
	'swiper',
]);

$prevButtonClass = Helpers::classnames([
	Helpers::selector($blockClass, $blockClass, 'button'),
	Helpers::selector($blockClass, $blockClass, 'button', 'previous'),
	Helpers::selector($blockJsClass, "{$blockJsClass}-prev-arrow"),
]);

$nextButtonClass = Helpers::classnames([
	Helpers::selector($blockClass, $blockClass, 'button'),
	Helpers::selector($blockClass, $blockClass, 'button', 'next'),
	Helpers::selector($blockJsClass, "{$blockJsClass}-next-arrow"),
]);

$paginationClass = Helpers::classnames([
	Helpers::selector($blockClass, $blockClass, 'pagination'),
	Helpers::selector($blockJsClass, "{$blockJsClass}-pagination"),
]);

?>
<div class="<?php echo esc_attr($wrapperClass); ?>">
	<?php if ($carouselShowPrevNext && $carouselEffect == 'coverflow' ) { ?>
		<button class="<?php echo esc_attr($prevButtonClass); ?>" aria-label="<?php echo esc_attr__('Previous slide', 'delta9-digital-blocks-plugin'); ?>">
			<?php echo $manifest['resources']['prevIcon']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
		</button>
	<?php } ?>
	<div
		class="<?php echo esc_attr($carouselClass); ?> swiper-container-effect_<?php echo esc_attr($carouselEffect ?: 'default'); ?>"
		data-swiper-loop="<?php echo esc_attr($carouselIsLoop ? 'true' : 'false'); ?>"
		data-show-items="<?php echo esc_attr($carouselShowItems); ?>"
		data-swiper-autoplay="<?php echo esc_attr($carouselIsAutoplay ? 'true' : 'false');  ?>"
		data-swiper-autoplay-delay="<?php echo esc_attr((string) $carouselAutoplayDelay); ?>"
		data-swiper-effect="<?php echo esc_attr($carouselEffect  ?: 'default');  ?>"
		data-swiper-centered="<?php echo esc_attr($carouselCenteredSlides ? 'true' : 'false'); ?>"
		data-swiper-space-between="<?php echo esc_attr((string) $carouselSpaceBetween); ?>"
		data-mobile-items="<?php echo esc_attr((string) $carouselMobileItems); ?>"
		style="--carousel-show-items: <?php echo esc_attr((string) max(1, (float) $carouselShowItems)); ?>; --carousel-space-between: <?php echo esc_attr((string) $carouselSpaceBetween); ?>px;"
	>
		<div class="swiper-wrapper swiper-effect_<?php echo esc_attr($carouselEffect ?: 'default'); ?>">
			<?php echo $renderContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
		</div>

		<?php if ($carouselShowPrevNext && $carouselEffect != 'coverflow' ) { ?>
			<button class="<?php echo esc_attr($prevButtonClass); ?>" aria-label="<?php echo esc_attr__('Previous slide', 'delta9-digital-blocks-plugin'); ?>">
				<?php echo $manifest['resources']['prevIcon']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
			</button>
			<button class="<?php echo esc_attr($nextButtonClass); ?>" aria-label="<?php echo esc_attr__('Next slide', 'delta9-digital-blocks-plugin'); ?>">
				<?php echo $manifest['resources']['nextIcon']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
			</button>
		<?php } ?>

		<?php if ($carouselShowPagination) { ?>
			<div class="<?php echo esc_attr($paginationClass); ?>"></div>
		<?php } ?>
	</div>
	<?php if ($carouselShowPrevNext && $carouselEffect == 'coverflow' ) { ?>
		<button class="<?php echo esc_attr($nextButtonClass); ?>" aria-label="<?php echo esc_attr__('Next slide', 'delta9-digital-blocks-plugin'); ?>">
			<?php echo $manifest['resources']['nextIcon']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
		</button>
	<?php } ?>
</div>
