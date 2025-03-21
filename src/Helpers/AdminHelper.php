<?php namespace Neonbug\Common\Helpers;

use App;
use Str;
use Cache;
use Request;
use Event;

//TODO this class isn't very pretty; refactor!
class AdminHelper {
	
	public function getListItems($repo_class)
	{
		$language      = App::make('Language');
		$resource_repo = App::make('ResourceRepository');
		$item_repo     = App::make($repo_class);
		
		$items = $item_repo->getForAdminList();
		$resource_repo->inflateObjectsWithValues($items, $language->id_language);
		
		return $items;
	}
	
	public function prepareFieldsForAdd($languages, $language_dependent_fields_config, 
		$language_independent_fields_config, $skip_resource_inflation = false)
	{
		return $this->prepareFieldsForAddEdit($languages, $language_dependent_fields_config, 
			$language_independent_fields_config, null, $skip_resource_inflation);
	}
	
	public function prepareFieldsForEdit($languages, $language_dependent_fields_config, 
		$language_independent_fields_config, $item, $skip_resource_inflation = false)
	{
		return $this->prepareFieldsForAddEdit($languages, $language_dependent_fields_config, 
			$language_independent_fields_config, $item, $skip_resource_inflation);
	}
	
	private function prepareFieldsForAddEdit($languages, $language_dependent_fields_config, 
		$language_independent_fields_config, $item = null, $skip_resource_inflation = false)
	{
		$values = ($item == null || $skip_resource_inflation ? [] : 
			App::make('ResourceRepository')->getValues($item->getTableName(), $item->{$item->getKeyName()}));
		
		$lang_dependent_fields = [];
		$lang_dependent_fields_config = $language_dependent_fields_config;
		if (sizeof($lang_dependent_fields_config) > 0)
		{
			foreach ($languages as $language)
			{
				$id_language = $language->id_language;
				$lang_dependent_fields[$id_language] = $lang_dependent_fields_config;
				
				for ($i=0; $i<sizeof($lang_dependent_fields[$id_language]); $i++)
				{
					$field = $lang_dependent_fields[$id_language][$i];
					
					$event = new \Neonbug\Common\Events\AdminAddEditPrepareField('dependent', $field, $item, $id_language);
					Event::dispatch($event);
					$lang_dependent_fields[$id_language][$i] = $event->field;
					
					if (array_key_exists($id_language, $values) && 
						array_key_exists($field['name'], $values[$id_language]))
					{
						$lang_dependent_fields[$id_language][$i]['value'] = $values[$id_language][$field['name']];
					}
					else if (array_key_exists($id_language, $values) && 
						array_key_exists('save_to_field', $field) && 
						$field['save_to_field'] != '' && 
						array_key_exists($field['save_to_field'], $values[$id_language]))
					{
						$lang_dependent_fields[$id_language][$i]['value'] = $values[$id_language][$field['save_to_field']];
					}
				}
			}
		}
		
		$fields = [
			'language_independent' => $language_independent_fields_config, 
			'language_dependent'   => $lang_dependent_fields
		];
			
		for ($i=0; $i<sizeof($fields['language_independent']); $i++)
		{
			$field = $fields['language_independent'][$i];
			
			$event = new \Neonbug\Common\Events\AdminAddEditPrepareField('independent', $field, $item);
			Event::dispatch($event);
			$fields['language_independent'][$i] = $event->field;
		}
		
		if ($item != null)
		{
			for ($i=0; $i<sizeof($fields['language_independent']); $i++)
			{
				$fields['language_independent'][$i]['value'] = $item->{$fields['language_independent'][$i]['name']};
			}
		}
		
		return $fields;
	}
	
	public function fillAndSaveItem($item, $fields, $allowed_language_independent_fields, 
		$allowed_language_dependent_fields)
	{
		$values = $this->fillItem($item, $fields, $allowed_language_independent_fields, 
			$allowed_language_dependent_fields);
		
		$item->touch();
		$item->save();
		
		if (sizeof(array_keys($values)) > 0)
		{
			$resource_repo = App::make('ResourceRepository');
			$resource_repo->setValues($item->getTableName(), $item->{$item->getKeyName()}, $values);
		}
	}
	
	public function fillItem($item, $fields, $allowed_language_independent_fields, 
		$allowed_language_dependent_fields)
	{
		$values = []; //language depedent values
		foreach ($fields as $id_language=>$field)
		{
			if ($id_language == -1) //general fields
			{
				foreach ($field as $field_name=>$field_value)
				{
					if (!in_array($field_name, $allowed_language_independent_fields)) continue;
					$item->$field_name = $field_value;
				}
			}
			else
			{
				if (!array_key_exists($id_language, $values))
				{
					$values[$id_language] = [];
				}
				foreach ($field as $field_name=>$field_value)
				{
					if (!in_array($field_name, $allowed_language_dependent_fields)) continue;
					$values[$id_language][$field_name] = $field_value;
				}
			}
		}
		
		return $values;
	}
	
	public function deleteItem($id, $model, $primary_key)
	{
		$event = new \Neonbug\Common\Events\AdminBeforeDeleteItem($id, $model, $primary_key);
		Event::dispatch($event);
		
		$model::where($primary_key, $id)
			->delete();
		
		App::make('ResourceRepository')
			->deleteValues(call_user_func($model . '::getTableName'), [ $id ]);
		
		$event = new \Neonbug\Common\Events\AdminAfterDeleteItem($id, $model, $primary_key);
		Event::dispatch($event);
	}
	
	//rendering
	public function adminList($package_name, Array $title, Array $fields, $prefix, $repo_class, $top_routes = [])
	{
		$items = App::make('\Neonbug\Common\Helpers\AdminHelper')
			->getListItems($repo_class);
		
		$params = [
			'package_name' => $package_name, 
			'title'        => $title, 
			'items'        => $items, 
			'fields'       => $fields, 
			'add_route'    => $prefix . '::admin::add', 
			'edit_route'   => $prefix . '::admin::edit', 
			'delete_route' => $prefix . '::admin::delete', 
			'route_prefix' => $prefix,
			'top_routes'   => $top_routes
		];
		
		return App::make('\Neonbug\Common\Helpers\CommonHelper')
			->loadAdminView('common', 'list', $params);
	}
	
	public function adminAdd($package_name, Array $title, Array $language_dependent_fields, 
		Array $language_independent_fields, Array $messages, $prefix, $model_name, $supports_preview)
	{
		$languages = App::make('LanguageRepository')->getAll();
		
		$fields = App::make('\Neonbug\Common\Helpers\AdminHelper')->prepareFieldsForAdd(
			$languages, 
			$language_dependent_fields, 
			$language_independent_fields
		);
		
		$event = new \Neonbug\Common\Events\AdminAddPreparedFields($model_name, $fields, null);
		Event::dispatch($event);
		$fields = $event->fields;
		
		$params = [
			'package_name'     => $package_name, 
			'title'            => $title, 
			'fields'           => $fields, 
			'messages'         => $messages, 
			'languages'        => $languages, 
			'check_slug_route' => $prefix . '::admin::check-slug', 
			'prefix'           => $prefix, 
			'item'             => null, 
			'model_name'       => $model_name, 
			'supports_preview' => $supports_preview
		];
		
		return App::make('\Neonbug\Common\Helpers\CommonHelper')->loadAdminView('common', 'add', $params);
	}
	
	public function adminEdit($package_name, Array $title, Array $language_dependent_fields, 
		Array $language_independent_fields, Array $messages, $prefix, $model_name, $item, $supports_preview)
	{
		$languages = App::make('LanguageRepository')->getAll();
		
		$fields = App::make('\Neonbug\Common\Helpers\AdminHelper')->prepareFieldsForEdit(
			$languages, 
			$language_dependent_fields, 
			$language_independent_fields, 
			$item
		);
		
		$event = new \Neonbug\Common\Events\AdminEditPreparedFields($model_name, $fields, $item);
		Event::dispatch($event);
		$fields = $event->fields;
		
		$params = [
			'package_name'     => $package_name, 
			'title'            => $title, 
			'fields'           => $fields, 
			'messages'         => $messages, 
			'languages'        => $languages, 
			'check_slug_route' => $prefix . '::admin::check-slug', 
			'prefix'           => $prefix, 
			'item'             => $item, 
			'model_name'       => $model_name, 
			'supports_preview' => $supports_preview
		];
		
		return App::make('\Neonbug\Common\Helpers\CommonHelper')->loadAdminView('common', 'add', $params);
	}
	
	public function handleAdminAddEdit(Array $fields, Array $files, $id_user, 
		Array $language_independent_fields, Array $language_dependent_fields, $prefix, $model_name, $item, 
		$route_postfix, $is_add = null)
	{
		if ($is_add === null) {
			$is_add = $route_postfix == 'add';
		}
		$errors = []; //[ 'general' => 'DB error' ];
		
		$map = function($field) {
			if (array_key_exists('save_to_field', $field) && $field['save_to_field'] != '')
			{
				return $field['save_to_field'];
			}
			return $field['name'];
		};
		$allowed_lang_independent_fields = array_map($map, $language_independent_fields);
		$allowed_lang_dependent_fields   = array_map($map, $language_dependent_fields);
		
		// fill missing languages
		if (!array_key_exists(-1, $fields))
		{
			$fields[-1] = [];
		}
		
		$languages = App::make('LanguageRepository')->getAll();
		foreach ($languages as $language)
		{
			if (!array_key_exists($language->id_language, $fields))
			{
				$fields[$language->id_language] = [];
			}
		}
		
		// nullify empty fields
		foreach ($language_independent_fields as $field)
		{
			if (array_key_exists(-1, $fields))
			{
				foreach ($fields[-1] as $field_name=>$field_value)
				{
					if ($field_name != $field['name']) continue;
					
					if ($field_value === '' && !($field['required'] ?? false))
					{
						$fields[-1][$field_name] = null;
					}
				}
			}
		}
		
		// handle files
		$file_fields = $this->handleFileUpload($fields, $files, $prefix);
		
		$all_fields = $fields;
		foreach ($file_fields as $id_language=>$file_field_arr)
		{
			if (!array_key_exists($id_language, $all_fields))
			{
				$all_fields[$id_language] = [];
			}
			foreach ($file_field_arr as $field_name=>$file_field)
			{
				$all_fields[$id_language][$field_name] = $file_field;
			}
		}
		
		$event = null;
		if ($is_add)
		{
			$event = new \Neonbug\Common\Events\AdminAddSavePreparedFields(
				$prefix, 
				$model_name, 
				$all_fields, 
				$allowed_lang_independent_fields, 
				$allowed_lang_dependent_fields, 
				$language_independent_fields, 
				$language_dependent_fields, 
				$item
			);
		}
		else
		{
			$event = new \Neonbug\Common\Events\AdminEditSavePreparedFields(
				$prefix, 
				$model_name, 
				$all_fields, 
				$allowed_lang_independent_fields, 
				$allowed_lang_dependent_fields, 
				$language_independent_fields, 
				$language_dependent_fields, 
				$item
			);
		}
		
		if ($event != null)
		{
			Event::dispatch($event);
			
			$all_fields                      = $event->fields;
			$allowed_lang_independent_fields = $event->language_independent_fields;
			$allowed_lang_dependent_fields   = $event->language_dependent_fields;
		}
		
		App::make('\Neonbug\Common\Helpers\AdminHelper')
			->fillAndSaveItem($item, $all_fields, $allowed_lang_independent_fields, $allowed_lang_dependent_fields);
		
		$event = new \Neonbug\Common\Events\AdminAddEditSavedItem(
			$item, 
			$all_fields, 
			$language_independent_fields, 
			$language_dependent_fields, 
			$languages, 
			(sizeof($errors) > 0),
			$is_add
		);
		Event::dispatch($event);
		
		if (sizeof($errors) > 0)
		{
			return redirect(route($prefix . '::admin::' . $route_postfix, 
				($is_add ? [] : [ $item->{$item->getKeyName()} ])))
				->withErrors($errors);
		}
		return redirect(route($prefix . '::admin::' . $route_postfix, 
			($is_add ? [] : [ $item->{$item->getKeyName()} ])))
			->with([
				'messages' => [ trans('common::admin.main.messages.saved') ]
			]);
	}
	
	public function handleAdminPreview(Array $fields, Array $files, $id_user, Array $language_independent_fields, 
		Array $language_dependent_fields, $prefix, $id_item = -1)
	{
		$errors = []; //[ 'general' => 'DB error' ];
		
		$map = function($field) { return $field['name']; };
		$allowed_lang_independent_fields = array_map($map, $language_independent_fields);
		$allowed_lang_dependent_fields   = array_map($map, $language_dependent_fields);
		
		//TODO handle files
		
		$key = Str::random(10);
		Cache::remember($prefix . '::admin::preview::' . $key, 10 * 60, function() use ($fields, $id_user, $id_item, 
			$allowed_lang_independent_fields, $allowed_lang_dependent_fields) { 
			return [
				'id_item'                         => $id_item, 
				'fields'                          => $fields, 
				'id_user'                         => $id_user, 
				'allowed_lang_independent_fields' => $allowed_lang_independent_fields, 
				'allowed_lang_dependent_fields'   => $allowed_lang_dependent_fields
			];
		});
		
		if (sizeof($errors) > 0)
		{
			return redirect(route($prefix . '::preview', [ $key ]))
				->withErrors($errors);
		}
		return redirect(route($prefix . '::preview', [ $key ]))
			->with([
				'messages' => [ trans('common::admin.main.messages.saved') ]
			]);
	}
	
	protected function handleFileUpload(Array $fields, Array $files, $directory)
	{
		$new_fields = [];
		
		// delete files
		foreach ($fields as $id_language=>$field_arr)
		{
			foreach ($field_arr as $field_name=>$file_arr)
			{
				if ($field_name != 'remove-file') continue;
				
				foreach ($file_arr as $file=>$val)
				{
					if (!array_key_exists($id_language, $new_fields))
					{
						$new_fields[$id_language] = [];
					}
					$new_fields[$id_language][$file] = '';
					//TODO delete file
				}
			}
		}
		
		// handle new files
		foreach ($files as $id_language=>$file_fields)
		{
			foreach ($file_fields as $field_name=>$file)
			{
				if ($files[$id_language][$field_name] == null) continue;
				if (!$file->isValid()) continue;
				
				$filename = $file->getClientOriginalName();
				//TODO we shouldn't know of 'uploads' directory here; move it to a config or sth
				
				if (file_exists('uploads/' . $directory . '/' . $filename))
				{
					$filename = time() . '_' . $filename;
				}
				$file->move('uploads/' . $directory, $filename);
				
				if (!array_key_exists($id_language, $new_fields))
				{
					$new_fields[$id_language] = [];
				}
				$new_fields[$id_language][$field_name] = $filename;
			}
		}
		
		return $new_fields;
	}
}
