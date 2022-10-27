<?php

declare(strict_types=1);

namespace Imi\Nacos\Service;

use Imi\App;
use Imi\Event\Event;
use Imi\Service\Contract\IService;
use Imi\Service\Discovery\Contract\IDiscoveryDriver;
use Imi\Service\Service;
use Imi\Util\ImiPriority;
use Yurun\Nacos\Client;
use Yurun\Nacos\ClientConfig;

if (interface_exists(IDiscoveryDriver::class))
{
    class NacosServiceDiscoveryDriver implements IDiscoveryDriver
    {
        protected Client $client;

        protected array $config = [];

        public function __construct(array $config)
        {
            $this->config = $config;
            // @phpstan-ignore-next-line
            $this->client = new Client(new ClientConfig($config['clientConfig'] ?? []), App::getBean('Logger')->getLogger());
            Event::on(['IMI.PROCESS.BEGIN', 'IMI.MAIN_SERVER.WORKER.START'], function () {
                $this->client->reopen();
            }, ImiPriority::IMI_MAX);
        }

        /**
         * @return IService[]
         */
        public function getInstances(string $serviceId): array
        {
            $config = $this->config;
            $response = $this->client->instance->list($serviceId, $config['groupName'] ?? 'DEFAULT_GROUP', $config['namespaceId'] ?? '', $config['clusters'] ?? '', true);
            $list = [];
            foreach ($response->getHosts() as $host)
            {
                $list[] = new Service($host->getInstanceId(), $serviceId, $host->getIp() . ':' . $host->getPort(), (float) $host->getWeight(), $host->getMetadata() ?? []);
            }

            return $list;
        }
    }
}
