<?php namespace Neonbug\Common\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class AdminAddEditPrepareField extends Event {

	use SerializesModels;
	
	public $type;
	public $field;
	public $item; //only when editing, not available in adding
	
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($type, $field, $item)
	{
		$this->type = $type;
		$this->field = $field;
		$this->item = $item;
	}
	
}
