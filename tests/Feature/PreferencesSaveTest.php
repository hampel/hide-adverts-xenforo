<?php namespace Tests\Feature;

use Hampel\Testing\Concerns\UsesDatabaseTransactions;
use Tests\TestCase;
use XF\Entity\User;

/**
 * Saves through the real account/preferences action against an existing member, inside a
 * transaction that is rolled back after each test.
 */
class PreferencesSaveTest extends TestCase
{
	use UsesDatabaseTransactions;

	private function existingMember(bool $prefersHidden): User
	{
		$user = $this->app()->finder('XF:User')
			->where('user_state', 'valid')
			->where('user_id', '>', 0)
			->with('Option', true)
			->fetchOne();

		if (!$user)
		{
			$this->markTestSkipped('Needs at least one valid member in the database.');
		}

		$user->Option->hide_adverts = $prefersHidden;
		$user->Option->save();

		return $user;
	}

	private function savePreferences(User $user, array $input): bool
	{
		// the parent action saves the rest of the form too, so send the member's own values back
		$input += [
			'user' => [
				'style_id' => $user->style_id,
				'language_id' => $user->language_id,
				'timezone' => $user->timezone,
				'visible' => $user->visible,
				'activity_visible' => $user->activity_visible,
			],
		];

		$reply = $this->callAction('XF:Account', 'preferences', 'public', $input);
		$this->assertReplyIsRedirect($reply);

		return (bool) $this->app()->db()->fetchOne(
			'SELECT hide_adverts FROM xf_user_option WHERE user_id = ?', $user->user_id
		);
	}

	public function test_member_without_permission_keeps_their_preference()
	{
		$user = $this->actingAs($this->existingMember(true), ['general' => ['view' => true]]);

		// the disabled checkbox is never submitted
		$this->assertTrue($this->savePreferences($user, []));
	}

	public function test_member_without_permission_cannot_change_it_by_submitting_the_field()
	{
		$user = $this->actingAs($this->existingMember(true), ['general' => ['view' => true]]);

		$this->assertTrue($this->savePreferences($user, ['option' => ['hide_adverts' => 0]]));
	}

	public function test_permitted_member_can_turn_hiding_off()
	{
		$user = $this->actingAs(
			$this->existingMember(true),
			['general' => ['view' => true, 'hampelHideAdverts' => true]]
		);

		// an unticked checkbox is not submitted either
		$this->assertFalse($this->savePreferences($user, []));
	}

	public function test_permitted_member_can_turn_hiding_on()
	{
		$user = $this->actingAs(
			$this->existingMember(false),
			['general' => ['view' => true, 'hampelHideAdverts' => true]]
		);

		$this->assertTrue($this->savePreferences($user, ['option' => ['hide_adverts' => 1]]));
	}
}
