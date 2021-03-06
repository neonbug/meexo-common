<?php namespace Neonbug\Common\Translation;

use Illuminate\Filesystem\Filesystem;

class MockLoader implements \Illuminate\Contracts\Translation\Loader {

	/**
	 * All of the namespace hints.
	 *
	 * @var array
	 */
	protected $hints = array();

	/**
	 * Create a new database loader instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
	}

	/**
	 * Load the messages for the given locale.
	 *
	 * @param  string  $locale
	 * @param  string  $group
	 * @param  string  $namespace
	 * @return array
	 */
	public function load($locale, $group, $namespace = null)
	{
		if (is_null($namespace) || $namespace == '*')
		{
			return $this->loadData($locale, $group, 'common');
		}

		return $this->loadNamespaced($locale, $group, $namespace);
	}

	/**
	 * Load a namespaced translation group.
	 *
	 * @param  string  $locale
	 * @param  string  $group
	 * @param  string  $namespace
	 * @return array
	 */
	protected function loadNamespaced($locale, $group, $namespace)
	{
		if (isset($this->hints[$namespace]))
		{
			$lines = $this->loadData($this->hints[$namespace], $locale, $group);

			return $this->loadNamespaceOverrides($lines, $locale, $group, $namespace);
		}

		return array();
	}

	/**
	 * Load a local namespaced translation group for overrides.
	 *
	 * @param  array  $lines
	 * @param  string  $locale
	 * @param  string  $group
	 * @param  string  $namespace
	 * @return array
	 */
	protected function loadNamespaceOverrides(array $lines, $locale, $group, $namespace)
	{
		return [];
	}

	/**
	 * Load a locale from a given path.
	 *
	 * @param  string  $path
	 * @param  string  $locale
	 * @param  string  $group
	 * @return array
	 */
	protected function loadData($path, $locale, $group)
	{
		return [];
	}

	/**
	 * Add a new namespace to the loader.
	 *
	 * @param  string  $namespace
	 * @param  string  $hint
	 * @return void
	 */
	public function addNamespace($namespace, $hint)
	{
		$this->hints[$namespace] = $hint;
	}

	/**
	 * Add a new JSON path to the loader.
	 *
	 * @param  string  $path
	 * @return void
	 */
	public function addJsonPath($path)
	{
		$this->jsonPaths[] = $path;
	}

	/**
	 * Get an array of all the registered namespaces.
	 *
	 * @return array
	 */
	public function namespaces()
	{
		return $this->hints;
	}
}
