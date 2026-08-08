<?php

namespace DoctorDanila\ScriptDoc\Core;

use DoctorDanila\ScriptDoc\Core\DTO\NodeDTO;

class Scanner
{
    /**
     * Ищет файл или директорию без учёта регистра
     * Возвращает полный путь к найденному объекту или null
     */
    private function findCaseInsensitive(string $dir, string $targetName): ?string
    {
        if (!is_dir($dir)) {
            return null;
        }
        $targetLower = strtolower($targetName);
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            if (strtolower($item) === $targetLower) {
                return $dir . DIRECTORY_SEPARATOR . $item;
            }
        }
        return null;
    }

    public function readFile(string $path): ?string
    {
        return (file_exists($path) && is_file($path)) ? file_get_contents($path) : null;
    }
}
