<?php

namespace Loot\Spinoza;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

final class CacheManager
{
    /**
     * @var string
     */
    private $cacheFile = 'spinoza-cache-files.json';

    /**
     * @var array
     */
    private $cachedData = [];

    public function __construct()
    {
        $this->cachedData = $this->getCacheFiles();
    }

    public function saveCacheState(): void
    {
        Storage::put($this->cacheFile, json_encode($this->cachedData));
    }

    private function getCacheFiles(): array
    {
        if (Storage::exists($this->cacheFile)) {
            return json_decode(Storage::get($this->cacheFile), true);
        }

        return [];
    }

    public function fileNotChanged(string $file): bool
    {
        return isset($this->cachedData[$file]['editedAt']) && $this->cachedData[$file]['editedAt'] === filemtime(app_path($file));
    }

    public function saveAnnotationsForFile(string $file, string $annotations): void
    {
        $this->cachedData[$file]['annotations'][] = $annotations;
        $this->saveCacheState();
    }

    public function saveEditedTimeForFile(string $file): void
    {
        $this->cachedData[$file]['editedAt'] = filemtime(app_path($file));
        $this->saveCacheState();
    }

    public function getAnnotationsForFile(string $file): array
    {
        return $this->cachedData[$file]['annotations'] ?? [];
    }
}
