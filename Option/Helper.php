<?php namespace Hampel\HideAdverts\Option;

class Helper
{
	public static function sortStringList(&$string)
	{
		$values = preg_split('/[\s,]+/', $string, -1, PREG_SPLIT_NO_EMPTY);
		$values = array_unique($values, SORT_NUMERIC);
		sort($values);
		$string = implode(', ', $values);
	}

	public static function stringListToArray($string)
	{
		if (empty(trim($string))) return [];

		return array_map('intval', array_map('trim', explode(',', $string)));
	}
}
