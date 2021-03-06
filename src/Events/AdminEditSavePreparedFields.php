<?php namespace Neonbug\Common\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class AdminEditSavePreparedFields extends Event {

	use SerializesModels;
	
	public $route_prefix;
	public $class_name;
	public $fields;
	public $language_independent_fields;
	public $language_dependent_fields;
	public $all_language_independent_fields;
	public $all_language_dependent_fields;
	public $item;
	
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($route_prefix, $class_name, $fields, $language_independent_fields, 
		$language_dependent_fields, $all_language_independent_fields, $all_language_dependent_fields, 
		$item)
	{
		$this->route_prefix                    = $route_prefix;
		$this->class_name                      = $class_name;
		$this->fields                          = $fields;
		$this->language_independent_fields     = $language_independent_fields;
		$this->language_dependent_fields       = $language_dependent_fields;
		$this->all_language_independent_fields = $all_language_independent_fields;
		$this->all_language_dependent_fields   = $all_language_dependent_fields;
		$this->item                            = $item;
	}
	
}
