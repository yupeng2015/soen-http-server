<?php


namespace Soen\Http\Server;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Soen\Http\Message\Factory\StreamFactory;
use Soen\Http\Message\Response;
use Soen\Http\Message\ServerRequest;
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
	public function __construct(ResponseInterface $response, $routerProvider)
	{
		$this->response = $response;
		$this->routerProvider = $routerProvider;
	}

	public function handle(ServerRequestInterface $request):ResponseInterface
	{
        if ($request->getSwooleRequest()->server['path_info'] == '/favicon.ico' || $request->getSwooleRequest()->server['request_uri'] == '/favicon.ico') {
            return $this->response;
        }
		$this->routeActive = $this->routerProvider->setRouteActive($request);
		$res = call_user_func($this->routeActive->getClassAction(true), '');
        $streamBody = (new StreamFactory())->createStream($res);
        $this->response->withBody($streamBody);
        $this->response->withContentType('text/html', 'utf-8');
		return $this->response;
	}

}