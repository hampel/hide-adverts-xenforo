<?php namespace Tests\Feature;

use Hampel\HideAdverts\XF\Template\Templater;
use Tests\TestCase;
use XF\Entity\User;

class AdsOverrideTest extends TestCase
{
	private const RENDERED = '<!-- advert -->';

	/**
	 * The add-on's templater, with the two core calls it falls through to replaced, so "the
	 * advert rendered" is a value only the pass-through can produce rather than the empty string
	 * core returns when a style defines no advert for the position - which is also what
	 * suppression returns.
	 */
	private function templater(): Templater
	{
		// resolving the templater applies the class extension, which defines XFCP_Templater
		$this->assertInstanceOf(Templater::class, $this->app()->templater());

		$templater = new class extends Templater
		{
			public function __construct() {}

			protected function getTemplateData($type, $template, $errorOnUnknown = true)
			{
				return ['macros' => ['container_header' => true]];
			}

			public function callMacro(
				$template, $name, array $arguments, array $globalVars, ?\XF\Template\MacroState $macroState = null
			)
			{
				return '<!-- advert -->';
			}
		};

		return $templater;
	}

	private function page(?string $containerKey = null): array
	{
		return ['xf' => ['reply' => ['containerKey' => $containerKey]]];
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->actingAsMember(['user_group_id' => User::GROUP_REG, 'secondary_group_ids' => '']);
		$this->setOptions([
			'hampelHideAdvertsDisabledNodes' => '5, 8',
			'hampelHideAdvertsDisabledUsergroups' => '',
		]);
	}

	public function test_advert_renders_in_a_node_that_is_not_listed()
	{
		$this->assertSame(self::RENDERED, $this->templater()->callAdsMacro('container_header', [], $this->page('node-6')));
	}

	public function test_advert_is_suppressed_in_a_listed_node()
	{
		$this->assertSame('', $this->templater()->callAdsMacro('container_header', [], $this->page('node-8')));
	}

	public function test_a_container_that_is_not_a_node_is_ignored()
	{
		$this->assertSame(self::RENDERED, $this->templater()->callAdsMacro('container_header', [], $this->page('category-5')));
	}

	public function test_advert_renders_when_there_is_no_reply()
	{
		$this->assertSame(self::RENDERED, $this->templater()->callAdsMacro('container_header', [], ['xf' => []]));
	}

	public function test_advert_is_suppressed_for_a_listed_user_group()
	{
		$this->setOption('hampelHideAdvertsDisabledUsergroups', (string) User::GROUP_REG);

		$this->assertSame('', $this->templater()->callAdsMacro('container_header', [], $this->page('node-6')));
	}

	public function test_advert_renders_for_a_user_group_that_is_not_listed()
	{
		$this->setOption('hampelHideAdvertsDisabledUsergroups', (string) User::GROUP_MOD);

		$this->assertSame(self::RENDERED, $this->templater()->callAdsMacro('container_header', [], $this->page('node-6')));
	}
}
