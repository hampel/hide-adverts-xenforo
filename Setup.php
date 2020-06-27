<?php namespace Hampel\HideAdverts;

use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;
use XF\AddOn\AbstractSetup;

class Setup extends AbstractSetup
{
	public function install(array $stepParams = [])
	{
		$this->schemaManager()->alterTable('xf_user_option', function(Alter $table)
		{
			$table->addColumn('hide_adverts', 'tinyint', 3)
			      ->setDefault(1)
				  ->comment('Addon Hide Adverts');
		});
	}

	public function upgrade(array $stepParams = [])
	{
		// TODO: Implement upgrade() method.
	}

	public function uninstall(array $stepParams = [])
	{
		$this->schemaManager()->alterTable('xf_user_option', function(Alter $table)
		{
			$table->dropColumns('hide_adverts');
		});
	}

}