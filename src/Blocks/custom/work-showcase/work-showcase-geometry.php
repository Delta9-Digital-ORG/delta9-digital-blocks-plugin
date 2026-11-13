<?php

/**
 * Slot geometry — PHP mirror of assets/scene/layout.js.
 *
 * MUST stay in lock-step with layout.js: the two produce the CSS custom-property
 * values that place the server-rendered <img> fallback exactly where the WebGL
 * tiles will land. All values are expressed as multiples of `1cqh` (1% of the
 * stage height) so the fallback works at any stage aspect without JS.
 *
 * @package Delta9DigitalBlocksPlugin
 */

declare(strict_types=1);

namespace Delta9DigitalBlocksPlugin\Blocks\WorkShowcase;

if (!\class_exists(Geometry::class)) {
	/**
	 * Perspective math shared with the front-end scene.
	 */
	final class Geometry
	{
		/**
		 * Load and cache the slot table from slots.json.
		 *
		 * @return array<string, mixed>
		 */
		public static function slots(): array
		{
			static $slots = null;
			if ($slots === null) {
				$raw = \file_get_contents(__DIR__ . '/slots.json');
				$slots = \is_string($raw) ? (array) \json_decode($raw, true) : [];
			}
			return $slots;
		}

		/**
		 * World-space height visible at a given z for the configured camera.
		 * visibleHeightAt(z) = 2 * (cameraZ - z) * tan(fov / 2)
		 */
		public static function visibleHeightAt(float $z): float
		{
			$camera = self::slots()['camera'] ?? ['z' => 6, 'fov' => 70];
			return 2 * ((float) $camera['z'] - $z) * \tan(\deg2rad((float) $camera['fov']) / 2);
		}

		/**
		 * Fit a plane of `aspect` (w/h) inside a maxWidth x maxHeight box.
		 *
		 * @return array{width: float, height: float}
		 */
		public static function fitPlane(float $aspect, float $maxWidth, float $maxHeight): array
		{
			$height = \min($maxHeight, $maxWidth / $aspect);
			$width = $height * $aspect;
			return ['width' => $width, 'height' => $height];
		}

		/**
		 * Project a slot to cqh-relative { x, y, w, h }, centred on the stage.
		 *
		 * @param array<string, mixed> $slot
		 * @return array{x: float, y: float, w: float, h: float}
		 */
		public static function projectSlot(array $slot, float $aspect = 0.0): array
		{
			[$x, $y, $z] = $slot['position'];
			$box = $aspect > 0 ? $aspect : (float) $slot['maxWidth'] / (float) $slot['maxHeight'];
			$fit = self::fitPlane($box, (float) $slot['maxWidth'], (float) $slot['maxHeight']);

			$vh = self::visibleHeightAt((float) $z);
			$unit = 100 / $vh; // cqh per world unit at this z

			return [
				'x' => self::round((float) $x * $unit),
				'y' => self::round(-1 * (float) $y * $unit), // screen y inverted vs world y
				'w' => self::round($fit['width'] * $unit),
				'h' => self::round($fit['height'] * $unit),
			];
		}

		private static function round(float $n): float
		{
			return \round($n * 1000) / 1000;
		}
	}
}
