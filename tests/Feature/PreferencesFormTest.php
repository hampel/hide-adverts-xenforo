<?php namespace Tests\Feature;

use Hampel\Testing\Concerns\UsesDatabaseTransactions;
use Tests\TestCase;
use XF\Mvc\Reply\AbstractReply;

/**
 * Renders the real account/preferences page for an existing member, inside a transaction, and
 * reads the checkbox the template modification adds to it.
 */
class PreferencesFormTest extends TestCase
{
	use UsesDatabaseTransactions;

	private const MODIFICATION = 'hampelHideAdvertsAccountPreferences';
	private const FIELD = 'name="option[hampel_hideadverts_hide_adverts]"';

	/** Never an empty array - see PreferencesSaveTest. */
	private const WITHOUT_PERMISSION = ['general' => ['view' => true]];
	private const WITH_PERMISSION = ['general' => ['view' => true, 'hampelHideAdverts' => true]];

	private function preferencesReply(array $permissions, bool $prefersHidden): AbstractReply
	{
		// a template error is otherwise written to the error log, and only hidden by the
		// transaction rolling it back
		$this->fakesErrors();

		// an existing member rather than a new one: creating a user inside a transaction deadlocks
		// with any other suite doing the same against a shared development install
		$member = $this->app()->finder('XF:User')
			->where('user_state', 'valid')
			->where('user_id', '>', 0)
			->with('Option', true)
			->fetchOne();

		if (!$member)
		{
			$this->markTestSkipped('Needs at least one valid member in the database.');
		}

		$member->Option->hampel_hideadverts_hide_adverts = $prefersHidden;
		$member->Option->save();

		$this->actingAs($member, $permissions);

		$reply = $this->dispatch('account/preferences');
		$this->assertReplyTemplate($reply, 'account_preferences');

		return $reply;
	}

	private function preferencesPage(array $permissions, bool $prefersHidden): string
	{
		return $this->renderReply($this->preferencesReply($permissions, $prefersHidden));
	}

	/** The checkbox's own <input> tag, so an attribute on a neighbouring field cannot satisfy a test. */
	private function checkbox(string $html): string
	{
		$found = preg_match('#<input[^>]*' . preg_quote(self::FIELD, '#') . '[^>]*>#', $html, $match);
		$this->assertSame(1, $found, 'the hide adverts checkbox is not on the page');

		return $match[0];
	}

	public function test_the_template_modification_applies()
	{
		$this->preferencesPage(self::WITH_PERMISSION, true);

		$this->assertTemplateModificationApplied(self::MODIFICATION);
	}

	public function test_the_checkbox_comes_from_the_template_modification()
	{
		$reply = $this->preferencesReply(self::WITH_PERMISSION, true);

		$with = $this->renderReply($reply);
		$without = $this->renderTemplateWithoutModification(self::MODIFICATION, $reply->getParams());

		$this->assertSee($with, self::FIELD, false);
		$this->assertSeeText($with, 'Advert options');

		// the same page, still rendered - the row it sits above is there - with only this add-on's
		// row missing
		$this->assertSeeText($without, 'Privacy options');
		$this->assertDontSee($without, self::FIELD, false);
		$this->assertDontSeeText($without, 'Advert options');
	}

	public function test_permitted_member_gets_an_enabled_checkbox_showing_their_preference()
	{
		$hidden = $this->checkbox($this->preferencesPage(self::WITH_PERMISSION, true));
		$this->assertStringContainsString('checked', $hidden);
		$this->assertStringNotContainsString('disabled', $hidden);

		$shown = $this->checkbox($this->preferencesPage(self::WITH_PERMISSION, false));
		$this->assertStringNotContainsString('checked', $shown);
		$this->assertStringNotContainsString('disabled', $shown);
	}

	public function test_member_without_permission_gets_a_disabled_checkbox()
	{
		$checkbox = $this->checkbox($this->preferencesPage(self::WITHOUT_PERMISSION, true));

		$this->assertStringContainsString('disabled', $checkbox);
	}

	public function test_the_labels_are_phrases_that_resolved()
	{
		$html = $this->preferencesPage(self::WITH_PERMISSION, true);

		// an unresolved phrase renders as its own key, so the text is the positive half
		$this->assertSeeText($html, 'Advert options');
		$this->assertSeeText($html, 'Hide adverts');
		$this->assertSeeText($html, 'Only available to members with permission to hide adverts');
		$this->assertNoUnresolvedPhrases($html, 'hampel_hideadverts_');
	}

	public function test_the_modified_template_renders_without_errors()
	{
		$html = $this->preferencesPage(self::WITH_PERMISSION, true);

		// scoped to the template the modification is compiled into: on an install shared with
		// other add-ons, their modifications to this page raise errors that are not this add-on's
		$this->checkbox($html);
		$this->assertNoTemplateErrors('public:account_preferences');
	}
}
