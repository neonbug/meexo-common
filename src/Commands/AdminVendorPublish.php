<?php namespace Neonbug\Common\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Neonbug\Common\Providers\BaseServiceProvider;
use Illuminate\Filesystem\Filesystem;

class AdminVendorPublish extends \Illuminate\Foundation\Console\VendorPublishCommand {

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'vendor:publish-admin {--force : Overwrite any existing files.}
            {--provider= : The service provider that has assets you want to publish.}
            {--tag=* : One or many tags that have assets you want to publish.}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish any publishable admin assets from vendor packages';

	/**
	 * Create a new command instance.
	 *
	 * @param  \Illuminate\Filesystem\Filesystem
	 * @return void
	 */
	public function __construct(Filesystem $files)
	{
		parent::__construct($files);
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$tags = $this->option('tag');

		$tags = $tags ?: [null];

		foreach ($tags as $tag) {
			$paths = BaseServiceProvider::pathsToPublishAdmin(
				$this->option('provider'), $tag
			);

			if (empty($paths))
			{
				return $this->comment("Nothing to publish.");
			}

			foreach ($paths as $from => $to)
			{
				if ($this->files->isFile($from))
				{
					$this->publishFile($from, $to);
				}
				elseif ($this->files->isDirectory($from))
				{
					$this->publishDirectory($from, $to);
				}
				else
				{
					$this->error("Can't locate path: <{$from}>");
				}
			}
		}

		$this->info('Publishing Complete!');
	}

}
