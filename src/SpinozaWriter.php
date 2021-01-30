<?php

namespace Loot\Spinoza;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Loot\PhpDocReader\PhpDocLine;
use Loot\PhpDocReader\PhpDocReader;
use Symfony\Component\Finder\SplFileInfo;

final class SpinozaWriter
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    const ROUTE_ANNOTATION = '@spinoza-register-route';
    const EVENT_ANNOTATION = '@spinoza-register-event';

    /**
     * Spinoza constructor.
     * @param bool $forceUpdate
     */
    public function __construct(bool $forceUpdate = false)
    {
        $this->cacheManager = app(CacheManager::class);

        if ($forceUpdate) {
            $this->cacheManager->initForceUpdate();
        }
    }

    private function getApplicationFiles(): array
    {
        /**
         * @var $files SplFileInfo[]
         */
        $findFiles = File::allFiles(app_path()); // @todo bottle-neck
        $files = [];

        foreach ($findFiles as $file) {
            $files[$file->getRelativePathname()] = $file->getMTime();
        }

        return $files;
    }

    public function collectData(): array
    {
        $collect = [
            'routes' => [],
            'events' => [],
        ];
        $files = $this->getApplicationFiles();
        $this->cacheManager->saveCacheFiles($files);

        foreach ($files as $file => $editDate) {
            // @todo Чтение из кеша!
            if (false) {//$this->cacheManager->fileNotChanged($file)) {
                //$cache->getforfile($file);
            } else {
                $source = file_get_contents(app_path($file));
                $tokens = token_get_all($source);

                foreach ($tokens as $token) {
                    if ($token[0] === T_DOC_COMMENT) {
                        $phpDocReader = new PhpDocReader($token[1]);

                        // @todo вынести все аннотации в отдельные классы
                        if ($phpDocReader->hasAnnotation(self::ROUTE_ANNOTATION)) {
                            foreach ($phpDocReader->getAnnotationsByName(self::ROUTE_ANNOTATION) as $annotation) {
                                /** @var PhpDocLine $annotation */
                                if (empty($collect['routes'][$annotation->getRouteId()])) {
                                    $collect['routes'][$annotation->getRouteId()] = $annotation->getDescription();
                                }

                                $collect['routes'][$annotation->getRouteId()]['possession'][] = $file;
                            }
                        }

                        if ($phpDocReader->hasAnnotation(self::EVENT_ANNOTATION)) {
                            foreach ($phpDocReader->getAnnotationsByName(self::EVENT_ANNOTATION) as $annotation) {
                                /** @var PhpDocLine $annotation */
                                $collect['events'][] = $annotation->getDescription();
                            }
                        }
                    }
                }
            }
        }

        return $collect;
    }

    /**
     * @param array $data
     */
    public function writeDocs(array $data)
    {
        if (!is_dir(base_path('docs'))) {
            mkdir(base_path('docs'));
        }

        $text = <<<MARKDOWN
## Подписан на события
| name  | exchange  |  routing_key |
|---|---|---|

MARKDOWN;

        foreach ($data['events'] as $event) {
            $text .= '|  '.$event['name'].' | '.$event['exchange'].'  |  '.$event['routing_key'].' |'.PHP_EOL;
        }

        $text .= <<<MARKDOWN


## Вызывает по HTTP
|method| route  | usage  |  Применяется в |
|---|---|---|---|

MARKDOWN;

        foreach ($data['routes'] as $route) {
            $text .= '|'.$route['method'].'|'.$route['route'].'|'.$route['usage'].'|'.implode(', ', ($route['possession'])).'|'.PHP_EOL;
        }

        File::put(base_path('docs/dependencies.md'), $text);
    }
}
