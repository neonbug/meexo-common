<?php namespace Neonbug\Common\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class AdminBeforeDeleteItem extends Event {

	use SerializesModels;
	
	public $id;
	public $model;
	public $primary_key;
	
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($id, $model, $primary_key)
	{
		$this->id          = $id;
		$this->model       = $model;
		$this->primary_key = $primary_key;
	}
	
}
