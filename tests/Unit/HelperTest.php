<?php namespace Tests\Unit;

use Hampel\HideAdverts\Option\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HelperTest extends TestCase
{
	public static function sortProvider(): array
	{
		return [
			'already tidy'           => ['3, 5, 10', '3, 5, 10'],
			'unsorted, numerically'  => ['10, 5, 3', '3, 5, 10'],
			'duplicates removed'     => ['5, 3, 5', '3, 5'],
			'spaces and commas mixed' => ["5 3,,10\n7", '3, 5, 7, 10'],
		];
	}

	#[DataProvider('sortProvider')]
	public function test_sort_string_list_normalises_the_option_value($input, $expected)
	{
		Helper::sortStringList($input);

		$this->assertSame($expected, $input);
	}

	public static function arrayProvider(): array
	{
		return [
			'empty'         => ['', []],
			'whitespace'    => ['   ', []],
			'one id'        => ['7', [7]],
			'several ids'   => ['3, 5, 10', [3, 5, 10]],
		];
	}

	#[DataProvider('arrayProvider')]
	public function test_string_list_to_array_returns_integers($input, $expected)
	{
		$this->assertSame($expected, Helper::stringListToArray($input));
	}
}
