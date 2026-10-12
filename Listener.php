<?php namespace Hampel\HideAdverts;

class Listener
{
	/**
	 * Declares which of this add-on's settings are secrets, for add-ons that report a forum's
	 * configuration with secrets removed. There are none: both options are lists of ids, and the
	 * add-on reads nothing from config.php.
	 *
	 * @param array $declarations
	 */
	public static function adminApiRedaction(array &$declarations)
	{
		$declarations['Hampel/HideAdverts'] = [];
	}
}
