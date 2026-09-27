<?php namespace Hampel\HideAdverts\Option;

use XF\Option\AbstractOption;

class DisabledUsergroups extends AbstractOption
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
		return Helper::stringListToArray(\XF::options()->hampelHideAdvertsDisabledUsergroups);
	}

	public static function inList($userGroupId)
	{
		return in_array($userGroupId, self::get());
	}
}
