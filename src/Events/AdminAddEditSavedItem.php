<?php namespace Neonbug\Common\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class AdminAddEditSavedItem extends Event {

	use SerializesModels;
	
	public $item;
	public $fields;
	public $language_independent_fields;
	public $language_dependent_fields;
	public $languages;
	public $is_error;
	
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($item, $fields, $language_independent_fields, 
		$language_dependent_fields, $languages, $is_error)
	{
		$this->item                        = $item;
		$this->fields                      = $fields;
		$this->language_independent_fields = $language_independent_fields;
		$this->language_dependent_fields   = $language_dependent_fields;
		$this->languages                   = $languages;
		$this->is_error                    = $is_error;
	}
	
}
