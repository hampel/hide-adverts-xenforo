<?php namespace Tests\Feature;

use Tests\TestCase;

/**
 * SecretsDeclarationTest calls the listener method directly. This fires the event the way another
 * add-on does, so it fails if the listener is not registered, or is registered against another
 * method or event.
 */
class ListenerWiringTest extends TestCase
{
	public function test_the_redaction_event_reaches_the_listener()
	{
		$declarations = [];

		$this->app()->fire('hampel_admin_api_redaction', [&$declarations]);

		$this->assertSame(['Hampel/HideAdverts' => []], $declarations);
	}
}
