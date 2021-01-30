<?php

namespace Loot\Spinoza;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

final class CacheManager
{
    /**
     * @var string
     */
    private $cacheFile = 'spinoza-cache-files.php';

    /**
     * @var array
     */
    private $cachedData = [];

    public function __construct()
    {
        $this->cachedData = $this->getCacheFiles();
    }

    public function initForceUpdate(): void
    {
        if (Storage::exists($this->cacheFile)) {
            Storage::delete($this->cacheFile);
        }
    }

    /**
     * @param array $files
     */
    public function saveCacheFiles(array $files): void
    {
        Storage::put($this->cacheFile, '<?php return '.var_export($files, true).';');
    }

    private function getCacheFiles(): array
    {
        if (Storage::exists($this->cacheFile)) {
            return require_once storage_path('app/'.$this->cacheFile);
        }

        return [];
    }

    public function fileNotChanged(string $file): bool
    {
        return Arr::get($this->cachedData, $file, 0) === filemtime(app_path($file));
    }
}
