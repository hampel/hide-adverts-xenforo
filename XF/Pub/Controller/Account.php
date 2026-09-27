<?php namespace Hampel\HideAdverts\XF\Pub\Controller;

class Account extends XFCP_Account
{
	protected function preferencesSaveProcess(\XF\Entity\User $visitor)
	{
		$form = parent::preferencesSaveProcess($visitor);

		// the checkbox is disabled for users without permission, so it is never submitted - saving
		// it anyway would silently reset their preference
		if ($visitor->canHideAdverts())
		{
			$input = $this->filter([
				'option' => [
					'hide_adverts' => 'bool',
				],
			]);

			$userOptions = $visitor->getRelationOrDefault('Option');
			$form->setupEntityInput($userOptions, $input['option']);
		}

		return $form;
	}
}
