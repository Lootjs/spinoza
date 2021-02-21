<?php

namespace Loot\Spinoza\Parsers;

use Loot\PhpDocReader\PhpDocLine;
use Loot\PhpDocReader\PhpDocReader;

final class RouteParser
{
    const ROUTE_ANNOTATION = '@spinoza-register-route';

    public function init(array $phpDocs, $file): array {
        $collect = [];

        foreach ($phpDocs as $phpDoc) {
            $phpDocReader = new PhpDocReader($phpDoc);

            if ($phpDocReader->hasAnnotation(self::ROUTE_ANNOTATION)) {
                foreach ($phpDocReader->getAnnotationsByName(self::ROUTE_ANNOTATION) as $annotation) {
                    /** @var PhpDocLine $annotation */
                    if (empty($collect[$annotation->getRouteId()])) {
                        $collect[$annotation->getRouteId()] = $annotation->getDescription();
                    }

                    $collect[$annotation->getRouteId()]['possession'][] = $file;
                }
            }
        }

        return $collect;
    }
}
