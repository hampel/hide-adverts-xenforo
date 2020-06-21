<?php namespace Hampel\HideAdverts\XF\Entity;

use XF\Mvc\Entity\Structure;

class UserOption extends XFCP_UserOption
{
	public static function getStructure(Structure $structure)
	{
		$structure = parent::getStructure($structure);

		$structure->columns['show_adverts'] = ['type' => self::BOOL, 'default' => false];

		return $structure;
	}
}
