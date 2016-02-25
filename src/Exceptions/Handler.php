<?php namespace Neonbug\Common\Exceptions;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Debug\ExceptionHandler as SymfonyDisplayer;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

class Handler { // extends ExceptionHandler {

	/**
	 * The log implementation.
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $log;

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException'
	];

	/**
	 * Create a new exception handler instance.
	 *
	 * @param  \Psr\Log\LoggerInterface  $log
	 * @return void
	 */
	public function __construct(LoggerInterface $log)
	{
		$this->log = $log;
	}

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Throwable  $e
	 * @return void
	 */
	public function report($e)
	{
		if ($e instanceof \Error)
		{
			$e = new \Exception('Exception occured: ' . $e->getMessage() . "\nOriginal stack trace: " . 
				$e->getTraceAsString(), $e->getCode());
		}
		if ($this->shouldntReport($e)) return;

		$this->log->error((string) $e);
	}

	/**
	 * Determine if the exception should be reported.
	 *
	 * @param  \Exception  $e
	 * @return bool
	 */
	public function shouldReport(Exception $e)
	{
		return ! $this->shouldntReport($e);
	}

	/**
	 * Determine if the exception is in the "do not report" list.
	 *
	 * @param  \Exception  $e
	 * @return bool
	 */
	protected function shouldntReport(Exception $e)
	{
		foreach ($this->dontReport as $type)
		{
			if ($e instanceof $type) return true;
		}

		return false;
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Throwable  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, $e)
	{
		if ($e instanceof \Error)
		{
			$e = new \Exception('Exception occured: ' . $e->getMessage() . "\nOriginal stack trace: " . 
				$e->getTraceAsString(), $e->getCode());
		}
		
		// If the request wants JSON (AJAX doesn't always want JSON)
		if ($request->wantsJson())
		{
			// Define the response
			$response = [
				'errors' => 'Sorry, something went wrong.'
			];

			// If the app is in debug mode
			if (config('app.debug'))
			{
				// Add the exception class name, message and stack trace to response
				$response['exception'] = get_class($e); // Reflection might be better here
				$response['message'] = $e->getMessage();
				$response['trace'] = $e->getTrace();
			}

			// Default response of 400
			$status = 400;

			// If this exception is an instance of HttpException
			if ($this->isHttpException($e))
			{
				// Grab the HTTP status code from the Exception
				$status = $e->getStatusCode();
			}

			// Return a JSON response with the response array and status code
			return response()->json($response, $status);
		}
		
		if ($this->isHttpException($e))
		{
			return $this->renderHttpException($e);
		}
		else
		{
			return (new SymfonyDisplayer(config('app.debug')))->createResponse($e);
		}
	}

	/**
	 * Render an exception to the console.
	 *
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @param  \Throwable  $e
	 * @return void
	 */
	public function renderForConsole($output, $e)
	{
		(new ConsoleApplication)->renderException($e, $output);
	}

	/**
	 * Render the given HttpException.
	 *
	 * @param  \Symfony\Component\HttpKernel\Exception\HttpException  $e
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function renderHttpException(HttpException $e)
	{
		$status = $e->getStatusCode();

		if (view()->exists("errors.{$status}"))
		{
			return response()->view("errors.{$status}", [], $status);
		}
		else
		{
			return (new SymfonyDisplayer(config('app.debug')))->createResponse($e);
		}
	}

	/**
	 * Determine if the given exception is an HTTP exception.
	 *
	 * @param  \Throwable  $e
	 * @return bool
	 */
	protected function isHttpException($e)
	{
		return $e instanceof HttpException;
	}

}
