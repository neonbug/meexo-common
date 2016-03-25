<?php namespace Neonbug\Common\Mail;

use Illuminate\Support\ServiceProvider;
use Neonbug\Common\Mail\TransportManager;

class MailServiceProvider extends \Illuminate\Mail\MailServiceProvider {

	/**
	 * Register the Swift Transport instance.
	 *
	 * @return void
	 */
	protected function registerSwiftTransport()
	{
		$this->app['swift.transport'] = $this->app->share(function($app)
		{
			return new TransportManager($app);
		});
	}

}
