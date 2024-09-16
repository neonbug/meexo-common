<?php namespace Neonbug\Common\Session;

use Illuminate\Support\Manager;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

class SessionManager extends \Illuminate\Session\SessionManager {
	/**
	 * Build the session instance.
	 *
	 * @param  \SessionHandlerInterface  $handler
	 * @return \Illuminate\Session\Store
	 */
	protected function buildSession($handler)
	{
		$app = $this->getContainer();
		if ($app['config']['session.encrypt'])
		{
			return new EncryptedStore(
				$app['config']['session.cookie'], $handler, $app['encrypter']
			);
		}
		else
		{
			return new Store($app['config']['session.cookie'], $handler);
		}
	}

}
