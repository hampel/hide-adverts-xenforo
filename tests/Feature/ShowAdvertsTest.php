<?php namespace Tests\Feature;

use Tests\TestCase;
use XF\Entity\User;

class ShowAdvertsTest extends TestCase
{
	private const CAN_HIDE = ['general' => ['hampelHideAdverts' => true]];

	private function member(int $userId, bool $canHide, bool $prefersHidden): User
	{
		$user = $this->buildVisitor([
			'user_id' => $userId,
			'username' => "Member{$userId}",
			'user_state' => 'valid',
			'user_group_id' => User::GROUP_REG,
		]);
		$this->setVisitorPermissions($user, $canHide ? self::CAN_HIDE : []);
		$user->Option->hide_adverts = $prefersHidden;

		return $user;
	}

	public function test_permitted_member_who_prefers_hidden_sees_no_adverts()
	{
		$member = $this->actingAs($this->member(10, true, true));

		$this->assertTrue($member->canHideAdverts());
		$this->assertFalse($member->show_adverts);
	}

	public function test_permitted_member_who_prefers_adverts_sees_them()
	{
		$member = $this->actingAs($this->member(10, true, false));

		$this->assertTrue($member->canHideAdverts());
		$this->assertTrue($member->show_adverts);
	}

	public function test_member_without_permission_sees_adverts_whatever_their_preference()
	{
		$member = $this->actingAs($this->member(10, false, true));

		$this->assertFalse($member->canHideAdverts());
		$this->assertTrue($member->show_adverts);
	}

	public function test_guest_sees_adverts_even_with_the_permission()
	{
		$guest = $this->actingAsGuest(self::CAN_HIDE);
		$guest->Option->hide_adverts = true;

		$this->assertFalse($guest->canHideAdverts());
		$this->assertTrue($guest->show_adverts);
	}

	public function test_answers_for_the_user_asked_about_not_the_visitor()
	{
		$permitted = $this->member(10, true, true);
		$other = $this->member(11, false, true);

		$this->actingAs($other);
		$this->assertTrue($permitted->canHideAdverts());
		$this->assertFalse($permitted->show_adverts);

		$this->actingAs($permitted);
		$this->assertFalse($other->canHideAdverts());
		$this->assertTrue($other->show_adverts);
	}
}
