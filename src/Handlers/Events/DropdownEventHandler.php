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
		$events->listen('Neonbug\Common\Events\AdminAddEditPrepareField', function($event) {
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
		
		$events->listen('Neonbug\Common\Events\AdminEditPreparedFields', function($event) {
			$fields = $event->fields['language_independent'];
			for ($i=0; $i<sizeof($fields); $i++)
			{
				$field = $fields[$i];
				if (array_key_exists('multiple', $field) && 
					$field['multiple'] === true && 
					array_key_exists('value', $field))
				{
					if (is_array($field['value'])) {
						$event->fields['language_independent'][$i]['value'] = $field['value'];
					}
					else {
						$separator = (array_key_exists('separator', $field) ? $field['separator'] : ';');
						$event->fields['language_independent'][$i]['value'] = explode($separator, $field['value']);
					}
				}
			}
			
			foreach ($event->fields['language_dependent'] as $id_language=>$fields)
			{
				for ($i=0; $i<sizeof($fields); $i++)
				{
					$field = $fields[$i];
					if (array_key_exists('multiple', $field) && 
						$field['multiple'] === true && 
						array_key_exists('value', $field))
					{
						if (is_array($field['value'])) {
							$event->fields['language_dependent'][$id_language][$i]['value'] = $field['value'];
						}
						else {
							$separator = (array_key_exists('separator', $field) ? $field['separator'] : ';');
							$event->fields['language_dependent'][$id_language][$i]['value'] = explode($separator, $field['value']);
						}
					}
				}
			}
		});
		
		$events->listen([
				'Neonbug\Common\Events\AdminAddSavePreparedFields', 
				'Neonbug\Common\Events\AdminEditSavePreparedFields'
			], function($event) {
			foreach (
				[
					'independent' => $event->all_language_independent_fields, 
					'dependent'   => $event->all_language_dependent_fields
				]
				as $type=>$all_fields)
			{
				foreach ($all_fields as $field)
				{
					if ($field['type'] != 'dropdown') continue;
					
					if (array_key_exists('multiple', $field) && $field['multiple'] === true)
					{
						$separator = (array_key_exists('separator', $field) ? $field['separator'] : ';');
						
						foreach ($event->fields as $id_language=>$fields)
						{
							if ($type == 'independent' && $id_language != -1) continue;
							
							if (array_key_exists($field['name'], $event->fields[$id_language]))
							{
								$arr = array_filter(
									$event->fields[$id_language][$field['name']],
									function($x) {
										return $x != '{ignore-placeholder}';
									}
								);
								if (($field['handle_as'] ?? null) == 'array') {
									$event->fields[$id_language][$field['name']] = $arr;
								}
								else {
									$event->fields[$id_language][$field['name']] = implode($separator, $arr);
								}
							}
						}
					}
				}
			}
		});
	}
}
