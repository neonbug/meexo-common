<?php namespace Neonbug\Common\Mail;

use Neonbug\Common\Mail\Transport\SendGridTransport;

class TransportManager extends \Illuminate\Mail\TransportManager {

	/**
	 * Create an instance of the SendGrid Swift Transport driver.
	 *
	 * @return \Illuminate\Mail\Transport\SendGridTransport
	 */
	protected function createSendGridDriver()
	{
		$config = $this->app['config']->get('services.sendgrid', array());

		return new SendGridTransport($config['secret']);
	}

}
