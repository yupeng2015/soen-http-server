<?php


namespace Soen\Http\Server;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Soen\Http\Message\Response;
use Soen\Http\Message\ServerRequest;
use Soen\Router\Provider;
use Soen\Router\RouteActive;
use Soen\Router\Router;

class RequestHandler implements RequestHandlerInterface
{
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
	public function __construct(ResponseInterface $response)
	{
		$this->response = $response;
		$this->router = \App::getComponent('router');
	}

	public function handle(ServerRequestInterface $request):ResponseInterface
	{
		$this->routeActive = $this->routerProvider->setRouteActive($request);
		var_dump($this->routeActive->getRoute());
		exit;
	}



}