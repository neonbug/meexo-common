<?php namespace Neonbug\Common\Traits;

use Event;

trait OrdTrait {
	
	protected static $booted_ord_trait = false;
	
	public static function bootOrdTrait()
	{
		if (self::$booted_ord_trait === true) return;
		self::$booted_ord_trait = true;
		
		//TODO remove this file for 1.0 release, since it's here just for backwards compatiblity
	}
	
}
