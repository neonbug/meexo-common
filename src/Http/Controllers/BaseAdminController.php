<?php namespace Neonbug\Common\Http\Controllers;

use App;
use Request;
use Auth;
use Cache;

abstract class BaseAdminController extends \App\Http\Controllers\Controller {
	
	protected $admin_helper;
	
	public function __construct()
	{
		$this->admin_helper = App::make('\Neonbug\Common\Helpers\AdminHelper');
	}
	
	abstract protected function getModel();
	abstract protected function getRepository();
	abstract protected function getConfigPrefix();
	abstract protected function getRoutePrefix();
	abstract protected function getPackageName();
	abstract protected function getListTitle();
	abstract protected function getAddTitle();
	abstract protected function getEditTitle();
	
	public function adminList()
	{
		return $this->admin_helper->adminList(
			$this->getPackageName(), 
			$this->getListTitle(), 
			config($this->getConfigPrefix() . '.list.fields'), 
			$this->getRoutePrefix(), 
			$this->getRepository()
		);
	}
	
	public function adminAdd()
	{
		return $this->admin_helper->adminAdd(
			$this->getPackageName(), 
			$this->getAddTitle(), 
			config($this->getConfigPrefix() . '.add.language_dependent_fields'), 
			config($this->getConfigPrefix() . '.add.language_independent_fields'), 
			session('messages', []), 
			$this->getRoutePrefix(), 
			$this->getModel(), 
			config($this->getConfigPrefix() . '.supports_preview', true)
		);
	}
	
	public function adminAddPost()
	{
		$is_preview = (Request::input('preview') !== null);
		
		$model = $this->getModel();
		$item = new $model();
		
		return $this->adminAddPostHandle(
			$is_preview, 
			$item, 
			Request::input('field', []), //first level keys are language ids, second level are field names
			(Request::file('field') == null ? [] : Request::file('field')), //first level keys are language ids, second level are field names
			Auth::user()->id_user, 
			config($this->getConfigPrefix() . '.add.language_independent_fields'), 
			config($this->getConfigPrefix() . '.add.language_dependent_fields'), 
			$this->getRoutePrefix()
		);
	}
	
	protected function adminAddPostHandle($is_preview, $item, $fields, $files, $id_user, $lang_independent_fields, 
		$lang_dependent_fields, $route_prefix, $route_postfix = 'add')
	{
		if ($is_preview)
		{
			return $this->adminAddPreviewPost();
		}
		
		$retval = $this->admin_helper->handleAdminAddEdit(
			$fields, 
			$files, 
			$id_user, 
			$lang_independent_fields, 
			$lang_dependent_fields, 
			$route_prefix, 
			$this->getModel(), 
			$item, 
			$route_postfix,
			true
		);
		
		Cache::forget($this->getPackageName() . '::items');
		
		return $retval;
	}
	
	private function adminAddPreviewPost()
	{
		$retval = $this->admin_helper->handleAdminPreview(
			Request::input('field', []), //first level keys are language ids, second level are field names
			(Request::file('field') == null ? [] : Request::file('field')), //first level keys are language ids, second level are field names
			Auth::user()->id_user, 
			config($this->getConfigPrefix() . '.add.language_independent_fields'), 
			config($this->getConfigPrefix() . '.add.language_dependent_fields'), 
			$this->getRoutePrefix()
		);
		
		return $retval;
	}
	
	public function adminEdit($id)
	{
		$model = $this->getModel();
		$item = $model::findOrFail($id);
		
		return $this->admin_helper->adminEdit(
			$this->getPackageName(), 
			$this->getEditTitle(), 
			config($this->getConfigPrefix() . '.edit.language_dependent_fields'), 
			config($this->getConfigPrefix() . '.edit.language_independent_fields'), 
			session('messages', []), 
			$this->getRoutePrefix(), 
			$this->getModel(), 
			$item, 
			config($this->getConfigPrefix() . '.supports_preview', true)
		);
	}
	
	public function adminEditPost($id)
	{
		$is_preview = (Request::input('preview') !== null);
		
		$model = $this->getModel();
		$item = $model::findOrFail($id);
		
		return $this->adminEditPostHandle(
			$is_preview, 
			$item, 
			Request::input('field', []), //first level keys are language ids, second level are field names
			(Request::file('field') == null ? [] : Request::file('field')), //first level keys are language ids, second level are field names
			Auth::user()->id_user, 
			config($this->getConfigPrefix() . '.add.language_independent_fields'), 
			config($this->getConfigPrefix() . '.add.language_dependent_fields'), 
			$this->getRoutePrefix()
		);
	}
	
	protected function adminEditPostHandle($is_preview, $item, $fields, $files, $id_user, $lang_independent_fields, 
		$lang_dependent_fields, $route_prefix, $route_postfix = 'edit')
	{
		if ($is_preview)
		{
			return $this->adminEditPreviewPost($item->{$item->getKeyName()});
		}
		
		$retval = $this->admin_helper->handleAdminAddEdit(
			$fields, 
			$files, 
			$id_user, 
			$lang_independent_fields, 
			$lang_dependent_fields, 
			$route_prefix, 
			$this->getModel(), 
			$item, 
			$route_postfix,
			false
		);
		
		Cache::forget($this->getPackageName() . '::item::' . $item->{$item->getKeyName()});
		Cache::forget($this->getPackageName() . '::items');
		
		return $retval;
	}
	
	private function adminEditPreviewPost($id)
	{
		$model = $this->getModel();
		$item = $model::findOrFail($id);
		
		$retval = $this->admin_helper->handleAdminPreview(
			Request::input('field', []), //first level keys are language ids, second level are field names
			(Request::file('field') == null ? [] : Request::file('field')), //first level keys are language ids, second level are field names
			Auth::user()->id_user, 
			config($this->getConfigPrefix() . '.add.language_independent_fields'), 
			config($this->getConfigPrefix() . '.add.language_dependent_fields'), 
			$this->getRoutePrefix(), 
			$id
		);
		
		return $retval;
	}
	
	public function adminDeletePost()
	{
		$model = $this->getModel();
		
		$id   = Request::input('id');
		$item = $model::findOrFail($id);
		
		$this->admin_helper->deleteItem($id, $model, $item->getKeyName());
		
		Cache::forget($this->getPackageName() . '::item::' . $item->{$item->getKeyName()});
		Cache::forget($this->getPackageName() . '::items');
		
		return [ 'success' => true ];
	}
	
	public function adminCheckSlugPost()
	{
		$id_language = Request::input('id_language');
		$value       = Request::input('value');
		$id_item     = Request::input('id_item', -1);
		
		$valid = !App::make('ResourceRepository')->slugExists($this->getRoutePrefix(), $id_language, $value, $id_item);
		
		return [ 'valid' => $valid ];
	}
	
	public function adminSaveItemOrderPost()
	{
		$model = $this->getModel();
		$interfaces = class_implements($model);
		if (!array_key_exists('Neonbug\Common\Traits\OrdTraitInterface', $interfaces)) return [ 'success' => false ];
		
		$ids = Request::input('ids');
		if ($ids === null || $ids == '') {
			return [ 'success' => false ];
		}
		
		$ids = explode(',', $ids);
		
		$ord = 0;
		foreach ($ids as $id) {
			$item = $model::find($id);
			if ($item === null) continue;
			
			$ord_fields = $model::getOrdFields();
			if (!is_array($ord_fields) || sizeof($ord_fields) == 0) continue;
			
			$ord += 10;
			foreach ($ord_fields as $ord_field) {
				$item->$ord_field = $ord;
			}
			$item->save();
			
			Cache::forget($this->getPackageName() . '::item::' . $item->{$item->getKeyName()});
		}
		
		Cache::forget($this->getPackageName() . '::items');
		
		return [ 'success' => true ];
	}
	
}
