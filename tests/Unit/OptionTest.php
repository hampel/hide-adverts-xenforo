<?php namespace Tests\Unit;

use Hampel\HideAdverts\Option\DisabledNodes;
use Hampel\HideAdverts\Option\DisabledUsergroups;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OptionTest extends TestCase
{
	public static function optionProvider(): array
	{
		return [
			'nodes'       => [DisabledNodes::class, 'hampelHideAdvertsDisabledNodes'],
			'user groups' => [DisabledUsergroups::class, 'hampelHideAdvertsDisabledUsergroups'],
		];
	}

	#[DataProvider('optionProvider')]
	public function test_saving_the_option_normalises_the_list($class, $optionId)
	{
		$value = "8 5,5\n12";

		$this->assertTrue($class::verifyOption($value, $this->makeEntity('XF:Option')));
		$this->assertSame('5, 8, 12', $value);
	}

	#[DataProvider('optionProvider')]
	public function test_an_empty_option_is_accepted_and_left_empty($class, $optionId)
	{
		$value = '';

		$this->assertTrue($class::verifyOption($value, $this->makeEntity('XF:Option')));
		$this->assertSame('', $value);
	}

	#[DataProvider('optionProvider')]
	public function test_the_option_is_read_back_as_a_list_of_ids($class, $optionId)
	{
		$this->setOption($optionId, '5, 8');

		$this->assertSame([5, 8], $class::get());
		$this->assertTrue($class::inList(8));
		$this->assertTrue($class::inList('8'));
		$this->assertFalse($class::inList(6));
	}

	#[DataProvider('optionProvider')]
	public function test_an_empty_option_lists_nothing($class, $optionId)
	{
		$this->setOption($optionId, '');

		$this->assertSame([], $class::get());
		$this->assertFalse($class::inList(0));
	}

	public function test_each_option_reads_its_own_setting()
	{
		$this->setOptions([
			'hampelHideAdvertsDisabledNodes' => '5',
			'hampelHideAdvertsDisabledUsergroups' => '9',
		]);

		$this->assertSame([5], DisabledNodes::get());
		$this->assertSame([9], DisabledUsergroups::get());
	}
}
