<?php


namespace Soen\Http\Server;


use Soen\Http\Message\Factory\StreamFactory;
use Soen\Http\Message\Response;

class AbnormalResuest
{
    public static function route404(Response $response){
        // 404 处理
        $content = '404 未找到页面';
        $body    = (new StreamFactory())->createStream($content);
        $response->withContentType('text/html', 'utf-8')
            ->withBody($body)
            ->withStatus(404);
    }
}