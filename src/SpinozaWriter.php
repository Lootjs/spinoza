<?php

namespace Loot\Spinoza;

use Illuminate\Support\Facades\Storage;

final class SpinozaWriter
{
    private $cacheFile = 'spinoza.php';

    /**
     * Spinoza constructor.
     * @param bool $forceUpdate
     */
    public function __construct(bool $forceUpdate = false)
    {
        if ($forceUpdate) {
            $this->initForceUpdate();
        }

        $classes = $this->getClassFiles();

    }

    private function initForceUpdate(): void
    {
        if (Storage::exists($this->cacheFile)) {
            Storage::delete($this->cacheFile);
        }
    }

    private function getClassFiles(): array
    {
        $cache = $this->getCacheFiles();

        $files = $this->openDir(app_path('Http'));

        Storage::put($this->cacheFile, json_encode($files));

        return $files;
        //dd($files);
    }

    private function getCacheFiles(): array
    {
        if (Storage::exists($this->cacheFile)) {
            return include($this->cacheFile);
        }

        return [];
    }

    private function openDir(string $dir): array
    {
        $files = [];

        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if (is_dir($entry)) {
                    //$files[] = $this->openDir($entry);
                } else {
                    $files[] = $entry;
                }
            }

            closedir($handle);
        }

        return $files;
    }
}
