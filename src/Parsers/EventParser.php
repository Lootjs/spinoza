<?php

namespace Loot\Spinoza\Parsers;

final class EventParser {
    /**
     * @return array
     */
    public static function init()
    {
        $collect = [];
        $data = parse_ini_file(base_path('docker/circus.ini'), true, INI_SCANNER_RAW);

        foreach ($data as $worker) {
            if (isset($worker['cmd'])) {
                $cmd = str_replace('php artisan ', '', $worker['cmd']);
                $cmdChunks = explode(' ', $cmd);

                $collect[] = static::parse($cmdChunks);
            }
        }

        return $collect;
    }

    /**
     * @param array $chunks
     * @return array
     */
    public static function parse(array $chunks): array
    {
        $data = [];
        array_shift($chunks);

        foreach ($chunks as $chunk) {
            $pair = explode('=', $chunk);

            if (count($pair) === 1) {
                $data['routing_key'] = $pair[0];
            } else {
                $key = str_replace('--', '', $pair[0]);
                $data[$key] = $pair[1];
            }
        }

        return $data;
    }
}
