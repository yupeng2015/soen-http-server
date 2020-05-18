<?php


namespace Soen\Http\Server\Middleware;


use Soen\Http\Server\Exception\TypeException;
use Soen\Http\Server\Middleware\RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class MiddlewareDispatcher
{
	/**
	 * @var MiddlewareInterface[]
	 */
	public $middleware;

	/**
	 * @var ServerRequestInterface
	 */
	public $request;

	/**
	 * @var ResponseInterface
	 */
	public $response;

	/**
	 * MiddlewareDispatcher constructor.
	 * @param array $middleware
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 */
	public function __construct(array $middleware, ServerRequestInterface $request, ResponseInterface $response)
	{
		$this->request  = $request;
		$this->response = $response;
		foreach ($middleware as $class) {
//		    if (is_callable($class)) {
//			    $request = $class($request, $response);
//			    (new Middleware())->process($request, $response);
//            }
			$object = new $class(
				$request,
				$response
			);
			if (!($object instanceof MiddlewareInterface)) {
				throw new TypeException("{$class} type is not '" . MiddlewareInterface::class . "'");
			}
			$this->middleware[] = $object;
		}
	}

	/**
	 * 调度
	 * @return ResponseInterface
	 */
	public function dispatch(): ResponseInterface
	{
		return (new RequestHandler($this->middleware, $this->response))->handle($this->request);
	}
}