<?php namespace Hampel\HideAdverts\XF\Template;

use Hampel\HideAdverts\Option\DisabledNodes;

class Templater extends XFCP_Templater
{
	public function callAdsMacro($position, array $arguments, array $globalVars)
	{
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
