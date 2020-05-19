<?php

namespace Soen\Http\Server;

use Psr\Http\Server\RequestHandlerInterface;
use Soen\Http\Message\Response;
use Soen\Http\Message\ServerRequest;

/**
 * Interface HandlerInterface
 * @package Mix\Http\Server
 */
interface HandlerInterface extends RequestHandlerInterface
{

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @return mixed
     */
    public function handle(ServerRequest $request, Response $response);

}
