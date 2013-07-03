<?php
namespace Destiny\Utils;

use Destiny\UserFeature;

abstract class Color {

	/**
	 * Builds colors depending on the user features
	 * @param array $user
	 * @param string $color Default color
	 */
	public static function getFeaturesColor($features, $color = '#0088CC') {
		if (! empty ( $features )) {
			if (in_array ( UserFeature::ADMIN, $features )) return '#EE0000';
			if (in_array ( UserFeature::MODERATOR, $features )) return '#FF8A00';
			if (in_array ( UserFeature::SUBSCRIBER, $features )) return '#54AAD2';
			if (in_array ( UserFeature::VIP, $features )) return '#CF31E2';
			if (in_array ( UserFeature::PROTECT, $features )) return '#CCCCCC';
		}
		return $color;
	}

}