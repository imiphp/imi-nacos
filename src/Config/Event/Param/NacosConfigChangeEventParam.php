<?php

declare(strict_types=1);

namespace Imi\Nacos\Config\Event\Param;

use Imi\ConfigCenter\Event\Param\ConfigChangeEventParam;
use Yurun\Nacos\Provider\Config\ConfigListener;

class NacosConfigChangeEventParam extends ConfigChangeEventParam
{
    protected ?ConfigListener $listener = null;

    protected string $group = '';

    protected string $tenant = '';

    public function __construct(string $eventName, array $data = [], ?object $target = null)
    {
        parent::__construct($eventName, $data, $target);
        $this->listener = $data['options']['listener'] ?? null;
        $this->group = $data['options']['group'] ?? 'DEFAULT_GROUP';
        $this->tenant = $data['options']['tenant'] ?? '';
    }

    public function getListener(): ?ConfigListener
    {
        return $this->listener;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getTenant(): string
    {
        return $this->tenant;
    }
}
