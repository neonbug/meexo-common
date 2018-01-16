<?php namespace Neonbug\Common\Handlers\Events;

use App;
use Hash;

class PasswordHashEventHandler
{
	/**
	* Register the listeners for the subscriber.
	*
	* @param  Illuminate\Events\Dispatcher  $events
	* @return void
	*/
	public function subscribe($events)
	{
		$events->listen('Neonbug\Common\Events\AdminAddSavePreparedFields', function($event) {
			$this->handlePreparedFieldsEvent($event);
		});
		
		$events->listen('Neonbug\Common\Events\AdminEditSavePreparedFields', function($event) {
			$this->handlePreparedFieldsEvent($event);
		});
		
		$events->listen('Neonbug\Common\Events\AdminEditPreparedFields', function($event) {
			$interfaces = class_implements($event->class_name);
			if (!array_key_exists('Neonbug\Common\Traits\PasswordTraitInterface', $interfaces)) return;
			
			$class_name = $event->class_name;
			$password_fields = $class_name::getPasswordFields();
			
			if (sizeof($password_fields) > 0)
			{
				foreach ($event->fields as $type=>$fields)
				{
					foreach ($fields as $idx=>$field)
					{
						$field_name = $field['name'];
						if (!in_array($field_name, $password_fields)) continue;
						
						$event->fields[$type][$idx]['value'] = '';
					}
				}
			}
		});
	}
	
	protected function handlePreparedFieldsEvent($event)
	{
		$interfaces = class_implements($event->class_name);
		if (!array_key_exists('Neonbug\Common\Traits\PasswordTraitInterface', $interfaces)) return;
		
		$class_name = $event->class_name;
		$password_fields = $class_name::getPasswordFields();
		
		if (sizeof($password_fields) > 0)
		{
			foreach ($event->fields as $id_language=>$fields)
			{
				foreach ($fields as $field_name=>$field_value)
				{
					if (!in_array($field_name, $password_fields)) continue;
					
					if ($field_value == '')
					{
						unset($event->fields[$id_language][$field_name]);
					}
					else
					{
						$event->fields[$id_language][$field_name] = Hash::make($field_value);
					}
				}
			}
		}
	}
}
