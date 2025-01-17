<?php

declare(strict_types=1);
/**
 * This file is part of KnowYourself.
 *
 * @link     https://www.zhiwotansuo.cn
 * @document https://github.com/kydever/work-wx-user/blob/main/README.md
 * @contact  l@hyperf.io
 * @license  https://github.com/kydever/work-wx-user/blob/main/LICENSE
 */
namespace HyperfTest\Stub;

use EasyWeChat\Work\Application;
use Han\Utils\Factory;
use Han\Utils\Utils\Date;
use Han\Utils\Utils\Model;
use Han\Utils\Utils\Sorter;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
use KY\WorkWxUser\WeChat\WeChatFactory;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ContainerStub
{
    public static function mockContainer(): ContainerInterface
    {
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturnUsing(function () {
            $file = BASE_PATH . '/.env.json';
            if (file_exists($file)) {
                $data = Json::decode(file_get_contents($file));
                return new Config([
                    'work_wx_user' => $data,
                ]);
            }

            return new Config([
                'is_mock' => true,
                'work_wx_user' => [
                    'corp_id' => 'xxx',
                    'agent_id' => 123123,
                    'secret' => 'xxx',
                    'stub' => [
                        'userid' => '18600000527',
                    ],
                ],
            ]);
        });

        $container->shouldReceive('get')->with(RequestInterface::class)->andReturn(\Mockery::mock(RequestInterface::class));
        $container->shouldReceive('get')->with(Application::class)->andReturnUsing(function () use ($container) {
            $isMockery = $container->get(ConfigInterface::class)->get('is_mock', false);
            if ($isMockery) {
                $config = $container->get(ConfigInterface::class)->get('work_wx_user');
                $application = new Application($config);
                $application->setHttpClient($client = \Mockery::mock(HttpClientInterface::class));
                $client->shouldReceive('request')->withAnyArgs()->andReturnUsing(function (string $method, string $url, array $options) {
                    $path = __DIR__ . '/json/' . $method . str_replace('/', '_', $url) . '.json';
                    $response = \Mockery::mock(ResponseInterface::class);
                    $response->shouldReceive('toArray')->andReturn(
                        Json::decode(file_get_contents($path))
                    );
                    $response->shouldReceive('getContent')->andReturn(
                        file_get_contents($path)
                    );
                    $response->shouldReceive('getHeaders')->andReturn([]);
                    return $response;
                });
                return $application;
            }
            return (new WeChatFactory())($container);
        });

        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturnNull();
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(new NullLogger());
        $container->shouldReceive('get')->with(Model::class)->andReturn(new Model());
        $container->shouldReceive('get')->with(Date::class)->andReturn(new Date());
        $container->shouldReceive('get')->with(Sorter::class)->andReturn(new Sorter());
        $container->shouldReceive('get')->with(Factory::class)->andReturn($container);
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnTrue();
        $container->shouldReceive('has')->with(FormatterInterface::class)->andReturnFalse();

        ApplicationContext::setContainer($container);

        return $container;
    }
}
