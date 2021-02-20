<?php

declare(strict_types=1);

namespace Loot\Spinoza\Issues;

use Psalm\CodeLocation;
use Psalm\Issue\PluginIssue;

final class HttpClientUsed extends PluginIssue
{
    public function __construct(CodeLocation $codeLocation)
    {
        parent::__construct('Cant find documentation', $codeLocation);
    }
}
