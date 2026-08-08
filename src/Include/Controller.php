<?php

namespace DoctorDanila\ScriptDoc\Include;

use DoctorDanila\ScriptDoc\Core\DocGenerator;

class Controller
{
    private string $projectName     = '';
    private string $docsDir         = 'docs';
    private ?string $projectRootDir = null;
    private ?string $swaggerPath    = null;
    private string $routePrefix     = '/docs';

    public function setProjectName(string $name): self
    {
        $this->projectName = $name;
        return $this;
    }

    public function setDocsDir(string $path): self
    {
        $this->docsDir = $path;
        return $this;
    }

    public function setProjectRootDir(string $path): self
    {
        $this->projectRootDir = $path;
        return $this;
    }

    public function setSwaggerPath(string $path): self
    {
        $this->swaggerPath = $path;
        return $this;
    }

    public function setRoutePrefix(string $prefix): self
    {
        $this->routePrefix = '/' . trim($prefix, '/');
        return $this;
    }

    public function handleRequest(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (strpos($uri, $this->routePrefix) === 0) {
            $route = substr($uri, strlen($this->routePrefix));

            // Если корень проекта не задан явно, берём родительскую директорию от docsDir
            $rootDir = $this->projectRootDir ?: dirname(realpath($this->docsDir) ?: $this->docsDir);

            $generator = new DocGenerator(
                $this->docsDir,
                $rootDir,
                $this->projectName,
                $this->swaggerPath,
                $this->routePrefix
            );

            $generator->render($route);
            exit;
        }
    }
}
