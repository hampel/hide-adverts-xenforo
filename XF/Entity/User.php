<?php namespace Hampel\HideAdverts\XF\Entity;

use XF\Mvc\Entity\Structure;

class User extends XFCP_User
{
	public function canHideAdverts()
	{
		$visitor = \XF::visitor();

		// guest
		if (!$visitor->user_id)
		{
			return false;
		}

		return $visitor->hasPermission('general', 'hampelHideAdverts');
	}

	public function getShowAdverts()
	{
		$visitor = \XF::visitor();

		if ($visitor->canHideAdverts())
		{
			// if the user can hide their adverts, then show the adverts only if their preference is to not hide them
			return !$visitor->Option->hide_adverts;
		}

		// user can't hide adverts - always show adverts
		return true;
	}

	public static function getStructure(Structure $structure)
	{
		$structure = parent::getStructure($structure);

		$structure->getters['show_adverts'] = true;

		return $structure;
	}
}
