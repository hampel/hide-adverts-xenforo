<?php namespace Hampel\HideAdverts\XF\Template;

use Hampel\HideAdverts\Option\DisabledNodes;
use Hampel\HideAdverts\Option\DisabledUsergroups;
use XF\Entity\User;

class Templater extends XFCP_Templater
{
	public function callAdsMacro($position, array $arguments, array $globalVars)
	{
		$disabledUserGroups = DisabledUsergroups::get();
		if (!empty($disabledUserGroups))
		{
			/** @var User $visitor */
			$visitor = $globalVars['xf']['visitor'];
			if ($visitor->isMemberOf($disabledUserGroups))
			{
				return '';
			}
		}

		$containerKey = $globalVars['xf']['reply']['containerKey'];
		if ($containerKey)
		{
			$key = explode('-', $containerKey);
			if (isset($key[0]) && $key[0] == 'node')
			{
				if (isset($key[1]) && DisabledNodes::inList($key[1]))
				{
					return '';
				}
			}
		}

		return parent::callAdsMacro($position, $arguments, $globalVars);
	}
}
