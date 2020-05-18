<?php


namespace Soen\Http\Server\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware implements PsrMiddlewareInterface
{
	function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		// TODO: Implement process() method.
	}
}