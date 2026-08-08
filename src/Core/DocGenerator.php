<?php

namespace DoctorDanila\ScriptDoc\Core;

class DocGenerator
{
    private string $docsDir;
    private string $projectRootDir;
    private string $projectName;
    private ?string $swaggerPath;
    private string $baseUri;

    public function __construct(
        string $docsDir,
        string $projectRootDir,
        string $projectName,
        ?string $swaggerPath,
        string $baseUri
    ) {
        $this->docsDir          = rtrim($docsDir, '/');
        $this->projectRootDir   = rtrim($projectRootDir, '/');
        $this->projectName      = $projectName;
        $this->swaggerPath      = $swaggerPath;
        $this->baseUri          = $baseUri;
    }

    public function render(string $route): void
    {
        $route = strtolower(trim($route, '/'));

        $scanner = new Scanner();$treeBuilder = new TreeBuilder($this->baseUri);$mdConverter = new MarkdownConverter();

        // 1. Формируем элементы навигации
        $treeNodes = $scanner->scanRootFiles($this->projectRootDir, $route,$this->swaggerPath);
        $docsFolderNode =$scanner->scanDocsFolder($this->docsDir,$route);

        if ($docsFolderNode !== null) {
            $treeNodes[] =$docsFolderNode;
        }

        $navigationTree = $treeBuilder->build($treeNodes);

        // 2. Роутинг и получение файла для вывода
        $targetFile = null;
        $isPlainText = false;

        if ($route === 'project-readme') {
            $targetFile =$this->projectRootDir . '/README.md';
        } elseif ($route === 'license') {
            foreach (['LICENSE.md', 'LICENSE', 'LICENSE.txt'] as $filename) {$path = $this->projectRootDir . '/' .$filename;
                if (file_exists($path)) {
                    $targetFile =$path;
                    $isPlainText = (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'md');
                    break;
                }
            }
        } elseif ($route === 'project-contributing') {
            foreach (['CONTRIBUTING.md', 'CONTRIBUTING', 'CONTRIBUTING.txt'] as $filename) {$path = $this->projectRootDir . '/' .$filename;
                if (file_exists($path)) {
                    $targetFile =$path;
                    $isPlainText = (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'md');
                    break;
                }
            }
        } else {
            if (empty($route) || is_dir($this->docsDir . '/' .$route)) {
                $targetFile =$this->docsDir . '/' . ($route ? $route . '/' : '') . 'README.md';
            } else {
                $targetFile = $this->docsDir . '/' .$route . '.md';
            }
        }

        $content = '';$isError = false;

        if ($targetFile !== null) {$mdContent = $scanner->readFile($targetFile);
            if ($mdContent !== null) {
                if ($isPlainText) {
                    $mdContent = "```text\n" . $mdContent . "\n```";
                }
                $content = $mdConverter->convert($mdContent);
            } else {
                $isError = true;
            }
        } else {
            $isError = true;
        }

        $this->outputView($navigationTree, $content, $isError);
    }

    private function outputView(string $navigationTree, string $content, bool $isError): void
    {
        $title = $this->projectName ? "Документация | {$this->projectName}" : "Документация";

        ob_start();
        if ($isError) {
            include __DIR__ . '/../../resources/views/error.php';
        } else {
            echo $content;
        }
        $renderedContent = ob_get_clean();

        include __DIR__ . '/../../resources/views/layout.php';
    }
}
