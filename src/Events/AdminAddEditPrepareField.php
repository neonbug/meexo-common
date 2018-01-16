<?php namespace Neonbug\Common\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class AdminAddEditPrepareField extends Event {

	use SerializesModels;
	
	public $type;
	public $field;
	public $item; //only when editing, not available in adding
	public $id_language; //is -1, when type = independent
	
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($type, $field, $item, $id_language = -1)
	{
		$this->type = $type;
		$this->field = $field;
		$this->item = $item;
		$this->id_language = $id_language;
	}
	
}
