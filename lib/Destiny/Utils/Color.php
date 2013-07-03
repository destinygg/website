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
			foreach ( $features as $feature ) {
				switch ($feature) {
					case UserFeature::ADMIN :
						$color = '#EE0000';
						break 2;
					case UserFeature::MODERATOR :
						$color = '#FF8A00';
						break 2;
					case UserFeature::SUBSCRIBER :
						$color = '#54AAD2';
						break 2;
					case UserFeature::VIP :
						$color = '#CF31E2';
						break 2;
					case UserFeature::PROTECT :
						$color = '#CCCCCC';
						break 2;
				}
			}
		}
		return $color;
	}

}