<?php namespace Hampel\HideAdverts\XF\Entity;

use XF\Mvc\Entity\Structure;

class UserOption extends XFCP_UserOption
{
	public static function getStructure(Structure $structure)
	{
		$structure = parent::getStructure($structure);

		$structure->columns['hide_adverts'] = ['type' => self::BOOL, 'default' => true];

		return $structure;
	}
}
