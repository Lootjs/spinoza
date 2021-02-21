<?php

namespace Loot\Spinoza;

use Illuminate\Support\Facades\File;
use Loot\Spinoza\Parsers\EventParser;
use Loot\Spinoza\Parsers\RouteParser;

final class SpinozaWriter
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var EventParser
     */
    private $eventParser;

    /**
     * @var RouteParser
     */
    private $routeParser;

    /**
     * Spinoza constructor.
     */
    public function __construct(CacheManager $cacheManager, EventParser $eventParser, RouteParser $routeParser)
    {
        $this->cacheManager = $cacheManager;
        $this->eventParser = $eventParser;
        $this->routeParser = $routeParser;
    }

    private function getApplicationFiles(): array
    {
        $findFiles = File::allFiles(app_path());
        $files = [];

        foreach ($findFiles as $file) {
            $files[$file->getRelativePathname()] = [];
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

        foreach ($files as $file => $value) {
            if ($this->cacheManager->fileNotChanged($file)) {
                $collect['routes'] = array_merge(
                    $collect['routes'],
                    $this->routeParser->init($this->cacheManager->getAnnotationsForFile($file), $file)
                );
            } else {
                $source = file_get_contents(app_path($file));
                $tokens = token_get_all($source);

                foreach ($tokens as $token) {
                    if ($token[0] === T_DOC_COMMENT) {
                        $this->cacheManager->saveAnnotationsForFile($file, $token[1]);
                        $collect['routes'] = array_merge(
                            $collect['routes'],
                            $this->routeParser->init([$token[1]], $file)
                        );
                    }
                }

                $this->cacheManager->saveEditedTimeForFile($file);
            }
        }

        $collect['events'] = $this->eventParser->init();

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
            if (empty($event['queue'])) {
                continue;
            }

            $text .= '|  '.$event['queue'].' | '.$event['exchange'].'  |  '.$event['routing_key'].' |'.PHP_EOL;
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
