<?php


namespace Soen\Http\Server\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
{
	/**
	 * @var MiddlewareInterface[]
	 */
	public $middleware;

	/**
	 * @var ResponseInterface
	 */
	public $response;

	/**
	 * RequestHandler constructor.
	 * @param array $middleware
	 * @param ResponseInterface $response
	 */
	public function __construct(array $middleware, ResponseInterface $response)
	{
		$this->middleware = $middleware;
		$this->response   = $response;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$middleware = array_shift($this->middleware);
		if (!$middleware) {
			return $this->response;
		}
		return $middleware->process($request, $this);
	}

}