<?php


namespace Soen\Http\Server;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Soen\Http\Message\Factory\StreamFactory;
use Soen\Http\Message\Response;
use Soen\Http\Message\ServerRequest;
use Soen\Http\Server\Middleware\MiddlewareDispatcher;
use Soen\Router\Provider;
use Soen\Router\RouteActive;
use Soen\Router\Router;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var Response
     */
	public $response;
	public $request;
	/**
	 * @var Provider
	 */
	public $routerProvider;
	/**
	 * @var RouteActive
	 */
	public $routeActive;

	/**
	 * RequestHandler constructor.
	 * @param ResponseInterface $response
	 * @param $routerProvider
	 */
	public function __construct(ResponseInterface $response, $routerProvider)
	{
		$this->response = $response;
		$this->routerProvider = $routerProvider;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface
	 * @throws Exception\TypeException
	 */
	public function handle(ServerRequestInterface $request):ResponseInterface
	{
        if ($request->getSwooleRequest()->server['path_info'] == '/favicon.ico' || $request->getSwooleRequest()->server['request_uri'] == '/favicon.ico') {
            return $this->response;
        }
		$this->routeActive = $this->routerProvider->setRouteActive($request);
        /* 中间件执行 */
		(new MiddlewareDispatcher($this->routeActive->getMiddleware(), $request, $this->response))->dispatch();
		$executeData = call_user_func($this->routeActive->getClassAction(true), '');
        $streamBody = (new StreamFactory())->createStream($executeData);
        $this->response->withBody($streamBody);
        $this->response->withContentType('text/html', 'utf-8');
		return $this->response;
	}

}