<?php


namespace Soen\Http\Server;


use Soen\Http\Message\Factory\ResponseFactory;
use Soen\Http\Message\Factory\ServerRequestFactory;
use Soen\Http\Message\Factory\ServerResponseFactory;
use Soen\Server\ServerInterface;

class Server implements ServerInterface
{

    /**
     * @var string
     */
    public $host = '0.0.0.0';

    /**
     * @var int
     */
    public $port = 9501;

    /**
     * @var bool
     */
    public $ssl = false;

    /**
     * @var bool
     */
    public $reusePort = false;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var []callable
     */
    protected $callbacks = [];

    /**
     * @var \Swoole\Coroutine\Http\Server
     */
    public $swooleServer;

    /**
     * HttpServer constructor.
     * @param string $host
     * @param int $port
     * @param bool $ssl
     * @param bool $reusePort
     */
    public function __construct(string $host, int $port, bool $ssl = false, bool $reusePort = false)
    {
        $this->host      = $host;
        $this->port      = $port;
        $this->ssl       = $ssl;
        $this->reusePort = $reusePort;
    }

    /**
     * Set
     * @param array $options
     */
    public function set(array $options)
    {
        $this->options = $options;
    }

    /**
     * Handle
     * @param string $pattern
     * @param callable $callback
     */
    public function handle(string $pattern, callable $callback)
    {
        $this->callbacks[$pattern] = $callback;
    }

    /**
     * 获取 url 规则映射的全部 service 名称
     *
     * Url                  Service        Method
     * /foo/bar             foo            Foo.Bar
     * /foo/bar/baz         foo            Bar.Baz
     * /foo/bar/baz/cat     foo.bar        Baz.Cat
     *
     * @return string[]
     */
    public function services()
    {
        $services = [];
        foreach (array_keys($this->callbacks) as $pattern) {
            $slice = array_filter(explode('/', $pattern));
            switch (count($slice)) {
                case 0:
                case 1:
                    $name = null;
                    break;
                case 2:
                case 3:
                    $name = array_shift($slice);
                    break;
                default:
                    array_pop($slice);
                    array_pop($slice);
                    $name = implode('/', $slice);
            }
            $name and $services[] = $name;
        }
        return $services;
    }

	/**
	 * @throws \Swoole\Exception
	 */
    public function up()
    {
	    $scheduler = new \Swoole\Coroutine\Scheduler;
//        if (!is_null($handler)) {
//            $this->callbacks = [];
//            $this->handle('/', [$handler, 'handleHTTP']);
//        }
	    $scheduler->set($this->options);
	    $scheduler->add(function () {
		    $server = $this->swooleServer = new \Swoole\Coroutine\Http\Server($this->host, $this->port, $this->ssl, $this->reusePort);
		    $server->set($this->options);
            $routerProvider = context()->getComponent('router');
		    $server->handle('/', function(\Swoole\Http\Request $requ, \Swoole\Http\Response $resp)use($routerProvider){
			    try {
				    $request = (new ServerRequestFactory)->createServerRequestFromSwoole($requ);
				    $response = (new ResponseFactory())->createResponseFromSwoole($resp);
				    $handler = (new RequestHandler($response, $routerProvider))->handle($request);
                    $handler->end();
			    }catch (\Throwable $error){
				    // 错误处理
				    throw $error;
			    }
		    });
		    if (!$server->start()) {
			    throw new \Swoole\Exception($server->errMsg, $server->errCode);
		    }
	    });
	    $scheduler->start();
    }

    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function down()
    {
        if (!$this->swooleServer) {
            return;
        }
        if (!$this->swooleServer->shutdown()) { // 返回 null
            $errMsg  = $this->swooleServer->errMsg;
            $errCode = $this->swooleServer->errCode;
            if ($errMsg == 'Operation canceled' && in_array($errCode, [89, 125])) { // mac=89, linux=125
                return;
            }
            throw new \Swoole\Exception($errMsg, $errCode);
        }
    }

}