<?php namespace Neonbug\Common\Handlers\Events;

use App;

class DropdownEventHandler
{
	protected $cache = [];
	
	/**
	* Register the listeners for the subscriber.
	*
	* @param  Illuminate\Events\Dispatcher  $events
	* @return void
	*/
	public function subscribe($events)
	{
		$events->listen('Neonbug\\Common\\Events\\AdminAddEditPrepareField', function($event) {
			if ($event->field['type'] != 'dropdown') return;
			
			if (array_key_exists('values', $event->field)) //static values
			{
				//do nothing, we already have the values
			}
			else if (array_key_exists('from', $event->field) && 
				array_key_exists('value_field', $event->field) && 
				array_key_exists('title_field', $event->field)) //values from a model
			{
				$class = $event->field['from'];
				if (array_key_exists($class, $this->cache))
				{
					$items = $this->cache[$class];
				}
				else
				{
					$items = $class::all();
					$this->cache[$class] = $items;
				}
				
				$language      = App::make('Language');
				$resource_repo = App::make('ResourceRepository');
				$resource_repo->inflateObjectsWithValues($items, $language->id_language);
				
				$value_field = $event->field['value_field'];
				$title_field = $event->field['title_field'];
				
				$values = [];
				foreach ($items as $item)
				{
					$values[$item->{$value_field}] = $item->{$title_field};
				}
				
				$event->field['values'] = $values;
			}
			else if (array_key_exists('repository', $event->field) && 
				array_key_exists('method', $event->field)) //values from a repository
			{
				$repo_class =  $event->field['repository'];
				$method = $event->field['method'];
				$key = $repo_class . '::' . $method;
				if (array_key_exists($key, $this->cache))
				{
					$values = $this->cache[$key];
				}
				else
				{
					$repo = App::make($repo_class);
					$values = $repo->$method();
					$this->cache[$key] = $values;
				}
				
				//remove the value of the item we're currently editing
				if (array_key_exists('skip_item_id', $event->field) && $event->field['skip_item_id'] === true && 
					$event->item != null && array_key_exists($event->item->{$event->item->getKeyName()}, $values))
				{
					unset($values[$event->item->{$event->item->getKeyName()}]);
				}
				
				$event->field['values'] = $values;
			}
			
			if (array_key_exists('default_value', $event->field))
			{
				$event->field['value'] = $event->field['default_value'];
			}
		});
	}
}
