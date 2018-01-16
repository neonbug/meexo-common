<?php namespace Neonbug\Common\Traits;

use App;
use Event;
use Hash;

trait PasswordTrait {
	
	protected static $booted_password_trait = false;
	
	public static function bootPasswordTrait()
	{
		if (self::$booted_password_trait === true) return;
		self::$booted_password_trait = true;
		
		//TODO remove this file for 1.0 release, since it's here just for backwards compatiblity
	}
	
}
