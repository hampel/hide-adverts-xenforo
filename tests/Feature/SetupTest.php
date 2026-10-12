<?php namespace Tests\Feature;

use Hampel\HideAdverts\Setup;
use Tests\TestCase;
use XF\Job\FileCleanUp;

/**
 * The rename step itself is not covered here: schema changes are not transactional, so it is proved
 * by an upgrade from a release zip on a disposable forum.
 */
class SetupTest extends TestCase
{
	private function setupClass(): Setup
	{
		return $this->app()->addOnManager()->getById('Hampel/HideAdverts')->getSetup();
	}

	public function test_the_preference_column_exists_under_its_prefixed_name_only()
	{
		$schema = $this->app()->db()->getSchemaManager();

		$this->assertTrue($schema->columnExists('xf_user_option', Setup::COLUMN, $definition));
		$this->assertFalse($schema->columnExists('xf_user_option', Setup::LEGACY_COLUMN));

		// the database default and the entity default have to agree, or members who have never
		// saved preferences behave differently from those who have
		$this->assertSame('1', (string) $definition['Default']);
		$this->assertSame('NO', $definition['Null']);
	}

	public function test_the_entity_declares_the_same_column_with_the_same_default()
	{
		$columns = $this->app()->em()->getEntityStructure('XF:UserOption')->columns;

		$this->assertArrayHasKey(Setup::COLUMN, $columns);
		$this->assertTrue($columns[Setup::COLUMN]['default']);
		$this->assertArrayNotHasKey(Setup::LEGACY_COLUMN, $columns);
	}

	public function test_post_upgrade_queues_the_file_clean_up_on_xenforo_2_3()
	{
		if (\XF::$versionId < 2030000)
		{
			$this->markTestSkipped('The clean-up job exists from XenForo 2.3.');
		}

		$this->fakesJobs();
		$stateChanges = [];

		$this->setupClass()->postUpgrade(1010270, $stateChanges);

		$this->assertJobQueued(FileCleanUp::class, function (array $job)
		{
			return $job['execute_data']['addon_id'] === 'Hampel/HideAdverts';
		});
	}
}
