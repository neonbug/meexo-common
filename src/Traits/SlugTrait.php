<?php namespace Neonbug\Common\Traits;

use App;
use Event;

trait SlugTrait {
	
	protected static $booted_slug_trait = false;
	
	public static function bootSlugTrait()
	{
		if (self::$booted_slug_trait === true) return;
		self::$booted_slug_trait = true;
		
		//TODO remove this file for 1.0 release, since it's here just for backwards compatiblity
	}
	
}
