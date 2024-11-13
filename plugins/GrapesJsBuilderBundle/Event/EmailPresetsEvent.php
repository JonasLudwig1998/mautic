<?php

namespace MauticPlugin\GrapesJsBuilderBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class EmailPresetsEvent extends Event
{
    public const EVENT_NAME = 'mautic.grapesjs.on_email_presets';

    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}