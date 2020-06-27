<?php namespace Hampel\HideAdverts\XF\Pub\Controller;

class Account extends XFCP_Account
{
	protected function preferencesSaveProcess(\XF\Entity\User $visitor)
	{
		$form = parent::preferencesSaveProcess($visitor);

		$input = $this->filter([
			'option' => [
				'hide_adverts' => 'bool',
			],
		]);

		if (!isset($input['option']['hide_adverts']))
		{
			$input['option']['hide_adverts'] = false;
		}

		$userOptions = $visitor->getRelationOrDefault('Option');
		$form->setupEntityInput($userOptions, $input['option']);

		return $form;
	}
}
