<?php

declare(strict_types=1);

namespace Imi\Nacos\Config;

use Imi\App;
use Imi\Event\Event;
use Imi\Nacos\Config\Contract\INacosConfigDriver;
use Imi\Nacos\Config\Event\Param\NacosConfigChangeEventParam;
use Imi\Util\ImiPriority;
use Yurun\Nacos\Client;
use Yurun\Nacos\ClientConfig;
use Yurun\Nacos\Provider\Config\ConfigListener;
use Yurun\Nacos\Provider\Config\Model\ListenerConfig;

class NacosConfigDriver implements INacosConfigDriver
{
    protected string $name = '';

    protected Client $client;

    protected array $config = [];

    protected ConfigListener $configListener;

    protected bool $listening = false;

    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->config = $config;
        // @phpstan-ignore-next-line
        $this->client = $client = new Client(new ClientConfig($config['client'] ?? []), App::getBean('Logger')->getLogger());
        Event::on(['IMI.PROCESS.BEGIN', 'IMI.MAIN_SERVER.WORKER.START'], function () {
            $this->client->reopen();
        }, ImiPriority::IMI_MAX);
        $listenerConfig = new ListenerConfig($config['listener'] ?? []);
        $this->configListener = $client->config->getConfigListener($listenerConfig);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function push(string $key, string $value, array $options = []): void
    {
        $this->client->config->set($key, $options['group'] ?? '', $value, $options['tenant'] ?? '', $options['type'] ?? '');
    }

    /**
     * {@inheritDoc}
     */
    public function pull(bool $enableCache = true): void
    {
        $this->configListener->pull(!$enableCache);
    }

    /**
     * 从配置中心获取配置原始数据.
     */
    public function getRaw(string $key, bool $enableCache = true, array $options = []): ?string
    {
        if ($enableCache)
        {
            return $this->configListener->get($key, $options['group'] ?? '', $options['tenant'] ?? '');
        }
        else
        {
            return $this->client->config->get($key, $options['group'] ?? '', $options['tenant'] ?? '') ?: '';
        }
    }

    /**
     * 从配置中心获取配置处理后的数据.
     *
     * @return mixed
     */
    public function get(string $key, bool $enableCache = true, array $options = [])
    {
        $type = $options['type'] ?? null;
        if ($enableCache)
        {
            return $this->configListener->getParsed($key, $options['group'] ?? '', $options['tenant'] ?? '', $type);
        }
        else
        {
            return $this->client->config->getParsedConfig($key, $options['group'] ?? '', $options['tenant'] ?? '', $type) ?: '';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete($keys, array $options = []): void
    {
        $group = $options['group'] ?? '';
        $tenant = $options['tenant'] ?? '';
        $config = $this->client->config;
        foreach ($keys as $key)
        {
            $config->delete($key, $group, $tenant);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function listen(string $imiConfigKey, string $key, array $options = []): void
    {
        $this->configListener->addListener($key, $options['group'] ?? '', $options['tenant'] ?? '', function (ConfigListener $listener, string $dataId, string $group, string $tenant) use ($imiConfigKey, $options) {
            $type = $options['type'] ?? null;
            Event::trigger('IMI.CONFIG_CENTER.CONFIG.CHANGE', [
                'driver'      => $this,
                'configKey'   => $imiConfigKey,
                'key'         => $dataId,
                'value'       => $listener->get($dataId, $group, $tenant),
                'parsedValue' => $listener->getParsed($dataId, $group, $tenant, $type),
                'options'     => [
                    'listener' => $listener,
                    'group'    => $group,
                    'tenant'   => $tenant,
                ],
            ], $this, NacosConfigChangeEventParam::class);
        });
    }

    /**
     * 执行一次轮询配置.
     */
    public function polling(): void
    {
        $this->configListener->polling(0);
    }

    /**
     * 开始监听配置.
     */
    public function startListner(): void
    {
        $this->listening = true;
        $this->configListener->start();
    }

    /**
     * 停止监听配置.
     */
    public function stopListner(): void
    {
        $this->listening = false;
        $this->configListener->stop();
    }

    /**
     * 是否正在监听.
     */
    public function isListening(): bool
    {
        return $this->listening;
    }

    public function isSupportServerPush(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getOriginClient(): Client
    {
        return $this->client;
    }
}
