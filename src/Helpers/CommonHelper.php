<?php namespace Neonbug\Common\Helpers;

class CommonHelper {
	
	public function loadView($package_name, $view_name, $params)
	{
		return view($this->resolveViewName($package_name, $view_name), $params);
	}
	
	public function resolveViewName($package_name, $view_name)
	{
		if (view()->exists($package_name . '::' . $view_name))
		{
			return $package_name . '::' . $view_name;
		}
		return $view_name;
	}
	
	public function loadAdminView($package_name, $view_name, $params)
	{
		return view($this->resolveAdminViewName($package_name, $view_name), $params);
	}
	
	public function resolveAdminViewName($package_name, $view_name)
	{
		if (view()->exists($package_name . '_admin' . '::' . $view_name))
		{
			return $package_name . '_admin' . '::' . $view_name;
		}
		return $view_name;
	}
	
}
