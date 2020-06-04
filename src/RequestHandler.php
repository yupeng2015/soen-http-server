<?php


namespace Soen\Http\Server;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Soen\Di\DiProvider;
use Soen\Http\Message\Factory\StreamFactory;
use Soen\Http\Message\Response;
use Soen\Http\Message\ServerRequest;
use Soen\Http\Server\Middleware\MiddlewareDispatcher;
use Soen\Router\Exception\NotFoundException;
use Soen\Router\Provider;
use Soen\Router\RouteCurrent;
use Soen\Router\Router;
use Soen\Router\RouterProvider;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var Response
     */
	public $response;
	public $request;
	/**
	 * @var RouterProvider
	 */
	public $routerProvider;
	/**
	 * @var RouteCurrent
	 */
	public $routeCurrent;

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
        try {
            $this->routeCurrent = $this->routerProvider->setRouteCurrent($request);
        } catch (NotFoundException $error) {
            // 404 处理
            AbnormalResuest::route404($this->response);
            return $this->response;
        }
        /* 中间件执行 */
		(new MiddlewareDispatcher($this->routeCurrent->getMiddlewares(), $request, $this->response))->dispatch();
        list($className, $action) = $this->routeCurrent->getClassAction();
//
//        $res = DiProvider::make($className, $action);
//        var_dump($res);
//        exit;
//        $reflectionMethod = new \ReflectionMethod($className, $action);
//        $args = [];
//        foreach($reflectionMethod->getParameters() as $parameter) {
//            if ($class = $parameter->getClass()) {
////                $args[] = new $class->name; //$request = new Request
//                $constructorParameters = $class->getConstructor()->getParameters();
//                foreach ($constructorParameters as $parameter){
//                    if($parameterClass = $parameter->getClass()){
//                        $parameterArr[] = new $parameterClass;
//                    }
//                }
//                exit;
//                $args[] = $class->newInstanceArgs(); //$request = new Request
//            }
//        }
//        $executeData = $reflectionMethod->invokeArgs(new $className, $args);


		$executeData = call_user_func_array($this->routeCurrent->getClassAction(true), $this->routeCurrent->getParams());
        $streamBody = (new StreamFactory())->createStream($executeData);
        $this->response->withBody($streamBody);
        $this->response->withContentType('text/html', 'utf-8');
		return $this->response;
	}

}