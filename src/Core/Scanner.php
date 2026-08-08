<?php

namespace DoctorDanila\ScriptDoc\Core;

use DoctorDanila\ScriptDoc\Core\DTO\NodeDTO;

class Scanner
{
    /**
     * Сканирует файлы в корне проекта (README, LICENSE, CONTRIBUTING, Swagger)
     */
    public function scanRootFiles(string $projectRootDir, string $currentUri, ?string $swaggerPath = null): array
    {
        $rootNodes = [];

        if ($this->findCaseInsensitive($projectRootDir, 'README.md')) {
            $node = new NodeDTO('README проекта', 'project-readme');
            $node->isActive = ($currentUri === 'project-readme');
            $node->isRoot = true;
            $rootNodes[] = $node;
        }

        foreach (['LICENSE.md', 'LICENSE', 'LICENSE.txt'] as $licenseFile) {
            if ($this->findCaseInsensitive($projectRootDir, $licenseFile) !== null) {
                $node = new NodeDTO('Лицензия проекта', 'license');
                $node->isActive = ($currentUri === 'license');
                $node->isRoot = true;
                $rootNodes[] = $node;
                break;
            }
        }

        if (!empty($swaggerPath)) {
            $node = new NodeDTO('Swagger документация', $swaggerPath, false, true);
            $rootNodes[] = $node;
        }

        foreach (['CONTRIBUTING.md', 'CONTRIBUTING', 'CONTRIBUTING.txt'] as $contribFile) {
            if ($this->findCaseInsensitive($projectRootDir, $contribFile) !== null) {
                $node = new NodeDTO('Contributing проекта', 'project-contributing');
                $node->isActive = ($currentUri === 'project-contributing');
                $node->isRoot = true;
                $rootNodes[] = $node;
                break;
            }
        }

        return $rootNodes;
    }

    /**
     * Сканирует папку docs и оборачивает её в узел директории
     */
    public function scanDocsFolder(string $docsDir, string $currentUri): ?NodeDTO
    {
        if (!is_dir($docsDir)) {
            return null;
        }

        $folderName = basename(rtrim($docsDir, '/\\'));
        $docsNode = new NodeDTO($folderName, '', true);
        $docsNode->children = $this->scanDirectory($docsDir, $docsDir, $currentUri);
        $docsNode->hasReadme = $this->findCaseInsensitive($docsDir, 'README.md') !== null;

        if ($currentUri === '' || $currentUri === 'readme') {
            $docsNode->isActive = true;
        }

        return $docsNode;
    }

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

    private function scanDirectory(string $currentDir, string $rootDir, string $currentUri): array
    {
        $nodes = [];
        $items = scandir($currentDir);

        foreach ($items as $item) {
            // Пропуск ссылок и скрытых файлов/папок
            if ($item === '.' || $item === '..' || strpos($item, '.') === 0) {
                continue;
            }

            $fullPath = $currentDir . DIRECTORY_SEPARATOR . $item;
            $relativePath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $fullPath);
            $relativePath = str_replace('\\', '/', $relativePath);

            $isDirectory = is_dir($fullPath);
            $node = new NodeDTO($item, $relativePath, $isDirectory);

            if ($isDirectory) {
                $node->children = $this->scanDirectory($fullPath, $rootDir, $currentUri);
                $node->hasReadme = $this->findCaseInsensitive($fullPath, 'README.md') !== null;

                // Если папка пуста и нет README - пропускаем
                if (empty($node->children) && !$node->hasReadme) {
                    continue;
                }
            } else {
                $extension = pathinfo($item, PATHINFO_EXTENSION);
                if (strtolower($extension) !== 'md') {
                    continue;
                }

                $node->name = pathinfo($item, PATHINFO_FILENAME);
                // Очищаем путь от расширения для роутинга
                $routePath = dirname($relativePath) === '.' ? $node->name : dirname($relativePath) . '/' . $node->name;
                $node->path = $routePath;
                $node->isActive = ($currentUri === strtolower($routePath));
            }

            $nodes[] = $node;
        }

        return $nodes;
    }

    public function readFile(string $path): ?string
    {
        return (file_exists($path) && is_file($path)) ? file_get_contents($path) : null;
    }
}
