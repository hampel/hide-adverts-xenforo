<?php namespace Tests;

use Hampel\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	/**
	 * Four levels up because this add-on id has a vendor in it (Hampel/HideAdverts):
	 * src/addons/Hampel/HideAdverts -> the install root. No trailing slash.
	 */
	protected $rootDir = '../../../..';

	/**
	 * Load only this add-on, so its class extensions are the only ones applied and other
	 * add-ons' listeners and vendor trees stay out of the run.
	 */
	protected $addonsToLoad = ['Hampel/HideAdverts'];
}
