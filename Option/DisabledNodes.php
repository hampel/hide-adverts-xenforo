<?php namespace Hampel\HideAdverts\Option;

use XF\Option\AbstractOption;

class DisabledNodes extends AbstractOption
{
	public static function verifyOption(&$value, \XF\Entity\Option $option)
	{
		if (!empty($value))
		{
			Helper::sortStringList($value);
		}

		return true;
	}

	public static function get()
	{
		return Helper::stringListToArray(\XF::options()->hampelHideAdvertsDisabledNodes);
	}

	public static function inList($nodeId)
	{
		return in_array($nodeId, self::get());
	}
}
