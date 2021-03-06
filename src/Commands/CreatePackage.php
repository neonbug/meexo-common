<?php namespace Neonbug\Common\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Str;
use App;

class CreatePackage extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'make:meexo-package';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a Meexo package';
	
	protected $view_factory;
	protected $neonbug_packages_path;
	protected $app_packages_path;
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Factory $view_factory, App $app)
	{
		parent::__construct();
		
		$this->view_factory          = $view_factory;
		$this->neonbug_packages_path = $app::basePath() . '/neonbug/';
		$this->app_packages_path     = $app::path() .     '/Packages/';
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		//gather information
		$name = Str::studly($this->argument('name'));
		
		$is_neonbug_package = 
			($this->choice('What type of package is this? Enter 0 or 1 [0]', ['app', 'neonbug'], '0') == 'neonbug');
		
		$neonbug_packages_path = $this->neonbug_packages_path;
		$packages_path         = ($is_neonbug_package ? $this->neonbug_packages_path : $this->app_packages_path);
		$namespace             = ($is_neonbug_package ? 'Neonbug' : 'App\\Packages');
		$config_root           = ($is_neonbug_package ? 'neonbug' : 'packages');
		
		$package_path          = $packages_path . $name . '/';
		$neonbug_package_path  = $neonbug_packages_path . $name . '/';
		if (file_exists($package_path) || file_exists($neonbug_package_path)) exit('Package already exists');
		
		$snake_package_name = Str::snake($name);
		$lowercase_snake_package_name = mb_strtolower($snake_package_name);
		
		$table_name    = $this->ask('Table name: [' .   $snake_package_name . ']', $snake_package_name);
		$route_prefix  = $this->ask('Route prefix: [' . $snake_package_name . ']', $snake_package_name);
		$config_prefix = $this->ask('Config name: [' .  $snake_package_name . ']', $snake_package_name);
		
		$model_name = Str::studly($table_name);
		
		//check with user if this is it
		$this->info('About to create a package:');
		$this->table([ 'Key', 'Value' ], [
			[ 'Package name',            $name ], 
			[ 'Table name',        $table_name ], 
			[ 'Route prefix',    $route_prefix ], 
			[ 'Config name',    $config_prefix ], 
			[ 'Model name',        $model_name ]
		]);
		if (!$this->confirm('Is this information correct, do you wish to continue (y/n)? [y]', true)) return;
		
		mkdir($package_path, 0755, true);
		
		//generate templates
		$template_path = __DIR__ . '/CreatePackage/';
		
		$create_table_migration_name = date('Y_m_d') . '_000100_create_' . $lowercase_snake_package_name . '_table.php';
		$insert_trans_migration_name = date('Y_m_d') . '_000101_insert_' . $lowercase_snake_package_name . '_translations.php';
		$trans_dir                   = 'database/migrations/translations.' . $lowercase_snake_package_name . '/';
		
		$templates = [
			'_create_table'        => [ 'dir' => 'database/migrations/', 'file'         => $create_table_migration_name ], 
			'_insert_translations' => [ 'dir' => 'database/migrations/', 'file'         => $insert_trans_migration_name ], 
			'_Controller'          => [ 'dir' => 'Controllers/',         'file'                     => 'Controller.php' ], 
			'_AdminController'     => [ 'dir' => 'Controllers/',         'file'                => 'AdminController.php' ], 
			'_Model'               => [ 'dir' => 'Models/',              'file'                 => $model_name . '.php' ], 
			'_ServiceProvider'     => [ 'dir' => 'Providers/',           'file'                => 'ServiceProvider.php' ], 
			'_config'              => [ 'dir' => 'config/',              'file'              => $config_prefix . '.php' ], 
			'_ModelRepository'     => [ 'dir' => 'Repositories/',        'file'       => $model_name . 'Repository.php' ], 
			'_trans_admin'         => [ 'dir' => $trans_dir,             'file'                          => 'admin.php' ], 
			'_trans_frontend'      => [ 'dir' => $trans_dir,             'file'                       => 'frontend.php' ], 
			'_index'               => [ 'dir' => 'resources/views/',     'file' => 'index.blade.php', 'prefix' => false ], 
			'_item'                => [ 'dir' => 'resources/views/',     'file'  => 'item.blade.php', 'prefix' => false ], 
		];
		
		foreach ($templates as $template_source=>$template_destination)
		{
			$filename = $template_path . $template_source . '.php';
			if (!file_exists($filename))
			{
				$filename = $template_path . $template_source . '.blade.php';
			}
			if (!file_exists($filename)) continue;
			
			$contents = $this->view_factory->file($filename)
				->with('namespace', 				$namespace)
				->with('package_name', 				$name)
				->with('lowercase_package_name', 	$lowercase_snake_package_name)
				->with('table_name', 				$table_name)
				->with('model_name', 				$model_name)
				->with('route_prefix', 				$route_prefix)
				->with('config_root', 				$config_root)
				->with('config_prefix', 			$config_prefix)
				->render();
			
			if (!array_key_exists('prefix', $template_destination) || $template_destination['prefix'] == true)
			{
				$contents = '<?php ' . $contents;
			}
			
			if (!file_exists($package_path . $template_destination['dir']))
			{
				mkdir($package_path . $template_destination['dir'], 0755, true);
			}
			file_put_contents($package_path . $template_destination['dir'] . $template_destination['file'], $contents);
		}
		
		//inform user we're done
		$step_idx = 1;
		
		$this->info('Done!');
		$this->line('You should now:');
		$this->line(($step_idx++) . ') add table columns in file ' . 
			realpath($packages_path . $name . '/database/migrations/' . $create_table_migration_name) . ', ');
		$this->line(($step_idx++) . ') add some fields in file ' . 
			realpath($packages_path . $name . '/config/' . $config_prefix . '.php') . ', ');
		$this->line(($step_idx++) . ') add translations in files ' . 
			realpath($packages_path . $name . '/' . $trans_dir . 'admin.php') . ' and ' . 
			realpath($packages_path . $name . '/' . $trans_dir . 'frontend.php') . ', ');
		$this->line(($step_idx++) . ') add ' . $namespace . '\\' . $name . '\Providers\ServiceProvider::class to ' . 
			'$package_providers array in file /config/app.php, ');
		
		if ($is_neonbug_package)
		{
			$this->line(($step_idx++) . ') add "autoload": ' . 
				'{ "psr-4": { "' . $namespace . '\\\\' . $name . '\\\\": "neonbug/' . $name . '" } } to composer.json, ');
			$this->line(($step_idx++) . ') run composer dump-autoload, ');
		}
		
		$this->line(($step_idx++) . ') run php artisan vendor:publish --all and php artisan migrate.');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'Name (e.g. News)'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
