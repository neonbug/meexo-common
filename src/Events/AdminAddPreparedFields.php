<?php namespace Neonbug\Common\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class AdminAddPreparedFields extends Event {

	use SerializesModels;
	
	public $class_name;
	public $fields;
	public $item;
	
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($class_name, $fields, $item)
	{
		$this->class_name = $class_name;
		$this->fields = $fields;
		$this->item = $item;
	}
	
}
