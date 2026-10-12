<?php namespace Tests\Feature;

use Hampel\Testing\Concerns\UsesDatabaseTransactions;
use Tests\TestCase;

/**
 * Renders the real account/preferences page for a member created inside a transaction, and reads
 * the checkbox the template modification adds to it.
 */
class PreferencesFormTest extends TestCase
{
	use UsesDatabaseTransactions;

	private const FIELD = 'name="option[hampel_hideadverts_hide_adverts]"';

	/** Never an empty array - see PreferencesSaveTest. */
	private const WITHOUT_PERMISSION = ['general' => ['view' => true]];
	private const WITH_PERMISSION = ['general' => ['view' => true, 'hampelHideAdverts' => true]];

	private function preferencesPage(array $permissions, bool $prefersHidden): string
	{
		// core's own render of this page logs template errors under test; without this they are
		// written to the error log and only hidden by the transaction rolling them back
		$this->fakesErrors();

		$member = $this->createUserAccount();
		$member->Option->hampel_hideadverts_hide_adverts = $prefersHidden;
		$member->Option->save();

		$this->actingAs($member, $permissions);

		$reply = $this->dispatch('account/preferences');
		$this->assertReplyTemplate($reply, 'account_preferences');

		return $this->renderReply($reply);
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

		$this->assertTemplateModificationApplied('hampelHideAdvertsAccountPreferences');
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
		$this->assertDontSee($html, 'hampel_hideadverts_options');
		$this->assertDontSee($html, 'hampel_hideadverts_hide_adverts_hint');
	}

	public function test_the_modified_template_renders_without_errors()
	{
		$html = $this->preferencesPage(self::WITH_PERMISSION, true);

		// scoped to the template the modification is compiled into: core's own render of this
		// page, and other add-ons' modifications to it, raise errors that are not this add-on's
		$this->checkbox($html);
		$this->assertNoTemplateErrors('public:account_preferences');
	}
}
