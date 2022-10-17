<?php

declare(strict_types=1);

namespace Imi\Nacos\Service;

use Imi\App;
use Imi\Event\Event;
use Imi\Service\Contract\IService;
use Imi\Service\ServiceRegistry\Contract\IServiceRegistry;
use Imi\Timer\Timer;
use Imi\Util\ImiPriority;
use Yurun\Nacos\Client;
use Yurun\Nacos\ClientConfig;
use Yurun\Nacos\Exception\NacosApiException;
use Yurun\Nacos\Provider\Instance\Model\RsInfo;

if (interface_exists(IServiceRegistry::class))
{
    class NacosServiceRegistry implements IServiceRegistry
    {
        protected Client $client;

        protected array $config = [];

        protected ?int $timerId = null;

        /**
         * @var IService[]
         */
        protected array $services = [];

        public function __construct(array $config)
        {
            $this->config = $config;
            // @phpstan-ignore-next-line
            $this->client = new Client(new ClientConfig($config['client'] ?? []), App::getBean('Logger')->getLogger());
            Event::on(['IMI.PROCESS.BEGIN', 'IMI.MAIN_SERVER.WORKER.START'], function () {
                $this->client->reopen();
            }, ImiPriority::IMI_MAX);
        }

        /**
         * {@inheritDoc}
         */
        public function register(IService $service): void
        {
            $metadata = $service->getMetadata();
            $serviceName = $service->getServiceId();
            $groupName = $metadata['group'] ?? 'DEFAULT_GROUP';
            $namespaceId = $metadata['namespaceId'] ?? '';
            $nacosMetadata = isset($metadata['metadata']) ? json_encode($metadata['metadata']) : '';
            $ephemeral = $metadata['ephemeral'] ?? true;
            if (!$ephemeral)
            {
                try
                {
                    $this->client->service->get($serviceName, $groupName, $namespaceId);
                }
                catch (NacosApiException $nae)
                {
                    // service 不存在自动创建
                    $this->client->service->create($serviceName, $groupName, $namespaceId, $metadata['protectThreshold'] ?? 0, $nacosMetadata, isset($metadata['selector']) ? json_encode($metadata['selector']) : '');
                }
                // 支持非临时实例注册
                $this->client->instance->register($service->getUri()->getHost(), $service->getUri()->getPort(), $serviceName, $namespaceId, $service->getWeight(), true, true, $nacosMetadata, $metadata['cluster'] ?? '', $groupName, $ephemeral);
            }
            $this->services[spl_object_id($service)] = $service;
            if (null === $this->timerId)
            {
                $this->startHeartbeatTimer();
            }
        }

        /**
         * {@inheritDoc}
         */
        public function deregister(IService $service): void
        {
            unset($this->services[spl_object_id($service)]);
            // 临时实例依赖心跳，非临时实例不需要注销，所以这里什么都不用做
        }

        protected function startHeartbeatTimer(): void
        {
            $this->timerId = Timer::tick((int) ($this->config['heartbeat'] ?? 10) * 1000, function () {
                foreach ($this->services as $service)
                {
                    $metadata = $service->getMetadata();
                    // 临时实例才需要心跳
                    if ($metadata['ephemeral'] ?? true)
                    {
                        $beat = new RsInfo([
                            'ip'          => $service->getUri()->getHost(),
                            'port'        => $service->getUri()->getPort(),
                            'serviceName' => $service->getServiceId(),
                            'cluster'     => $metadata['cluster'] ?? '',
                            'weight'      => $service->getWeight(),
                            'ephemeral'   => true,
                            'metadata'    => $metadata['metadata'] ?? null,
                        ]);
                        $this->client->instance->beat($service->getServiceId(), $beat, $metadata['group'] ?? 'DEFAULT_GROUP', $metadata['namespaceId'] ?? '', true);
                    }
                }
            });
        }
    }
}
