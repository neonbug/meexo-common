<?php namespace Neonbug\Common\Http\Middleware;

use Closure;
use Redirect;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware {

	/**
	 * The URIs that should be excluded from CSRF verification.
	 *
	 * @var array
	 */
	protected $except = [
		//
	];
	
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 *
	 * @throws \Illuminate\Session\TokenMismatchException
	 */
	public function handle($request, Closure $next)
	{
		if (
			$this->isReading($request) ||
			$this->runningUnitTests() ||
			$this->inExceptArray($request) ||
			($request->session()->has('_token') && $this->tokensMatch($request))
		) {
			return tap($next($request), function ($response) use ($request) {
				if ($this->shouldAddXsrfTokenCookie() && $this->isReading($request) && $request->session()->has('_token')) {
					$this->addCookieToResponse($request, $response);
				}
			});
		}

		//throw new TokenMismatchException('CSRF token mismatch.');
		return Redirect::back()->withInput()->withErrors([ 'general' => 'Your session has expired' ]);
	}

}
