namespace {{ $namespace }}\{{ $package_name }}\Repositories;

use Neonbug\Common\Models\Language;
use Neonbug\Common\Repositories\ResourceRepository;

class {{ $model_name }}Repository {
	
	const CONFIG_PREFIX = '{{ $config_root }}.{{ $config_prefix }}';
	
	protected $latest_items_limit = 20;
	protected $model;
	
	protected $language;
	protected $resource_repository;
	
	public function __construct(Language $language = null, ResourceRepository $resource_repository = null)
	{
		$this->model = config(static::CONFIG_PREFIX . '.model');
		
		$this->language            = $language;
		$this->resource_repository = $resource_repository;
	}
	
	public function getLatest()
	{
		$model = $this->model;
		return $model::orderBy('updated_at', 'DESC')
			->limit($this->latest_items_limit)
			->get();
	}
	
	public function getForAdminList()
	{
		$model = $this->model;
		return $model::all();
	}
	
	public function getAll()
	{
		$model = $this->model;
		
		$items = $model::orderBy('updated_at', 'ASC')
			->get();
		
		$this->resource_repository->inflateObjectsWithValues($items, $this->language->id_language);
		
		return $items;
	}
	
}
