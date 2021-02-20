<?php

declare(strict_types=1);

namespace Loot\Spinoza;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

final class Plugin implements PluginEntryPointInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(RegistrationInterface $api, SimpleXMLElement $config = null): void
    {
        require_once __DIR__ . '/Hooks/PreventWrongHttpClientUsage.php';
        $api->registerHooksFromClass(Hooks\PreventWrongHttpClientUsage::class);
    }
}
