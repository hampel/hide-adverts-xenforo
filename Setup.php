<?php namespace Hampel\HideAdverts;

use XF\Db\Schema\Alter;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{
	use StepRunnerUpgradeTrait;

	/**
	 * The preference column on xf_user_option. Columns added to core tables must carry an add-on
	 * identifier; versions before 1.1.4 used the unprefixed name below.
	 */
	const COLUMN = 'hampel_hideadverts_hide_adverts';
	const LEGACY_COLUMN = 'hide_adverts';

	public function install(array $stepParams = [])
	{
		$this->schemaManager()->alterTable('xf_user_option', function(Alter $table)
		{
			$table->addColumn(self::COLUMN, 'tinyint', 3)
			      ->setDefault(1)
				  ->comment('Addon Hide Adverts');
		});
	}

	// upgrade to 1.1.4 - rename the column, keeping every member's value
	public function upgrade1010412Step1(array $stepParams = [])
	{
		$sm = $this->schemaManager();

		// only when the old column is still there and the new one is not, so a re-run is harmless
		if ($sm->columnExists('xf_user_option', self::LEGACY_COLUMN)
			&& !$sm->columnExists('xf_user_option', self::COLUMN))
		{
			$sm->alterTable('xf_user_option', function(Alter $table)
			{
				$table->renameColumn(self::LEGACY_COLUMN, self::COLUMN);
			});
		}
	}

    public function postUpgrade($previousVersion, array &$stateChanges)
    {
        if (\XF::$versionId >= 2030000) { // XF 2.3+
            $this->enqueuePostUpgradeCleanUp();
        }
    }

	public function uninstall(array $stepParams = [])
	{
		$sm = $this->schemaManager();

		// either name, in case an upgrade to 1.1.4 never completed
		foreach ([self::COLUMN, self::LEGACY_COLUMN] as $column)
		{
			if ($sm->columnExists('xf_user_option', $column))
			{
				$sm->alterTable('xf_user_option', function(Alter $table) use ($column)
				{
					$table->dropColumns($column);
				});
			}
		}
	}
}
