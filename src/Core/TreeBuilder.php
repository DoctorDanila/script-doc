<?php

namespace DoctorDanila\ScriptDoc\Core;

use DoctorDanila\ScriptDoc\Core\DTO\NodeDTO;

class TreeBuilder
{
    private string $baseUri;
    private string $toggleSvg = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.47001 13.03C5.32956 12.8893 5.25067 12.6987 5.25067 12.5C5.25067 12.3012 5.32956 12.1106 5.47001 11.97L9.44001 7.99997L5.47001 4.02997C5.33753 3.88779 5.26541 3.69975 5.26884 3.50545C5.27226 3.31114 5.35098 3.12576 5.48839 2.98835C5.6258 2.85093 5.81119 2.77222 6.00549 2.76879C6.19979 2.76537 6.38784 2.83749 6.53001 2.96997L11.03 7.46997C11.1705 7.61059 11.2494 7.80122 11.2494 7.99997C11.2494 8.19872 11.1705 8.38934 11.03 8.52997L6.53001 13.03C6.38939 13.1704 6.19876 13.2493 6.00001 13.2493C5.80126 13.2493 5.61064 13.1704 5.47001 13.03Z" fill="#F3F8FF"/></svg>';

    public function __construct(string $baseUri = '/docs/')
    {
        $this->baseUri = rtrim($baseUri, '/') . '/';
    }

    /**
     * @param NodeDTO[] $nodes
     * @param bool $isRoot
     * @param bool $isOpen
     * @return string
     */
    public function build(array $nodes, bool $isRoot = true, bool $isOpen = false): string
    {
        if (empty($nodes)) {
            return '';
        }

        if ($isRoot) {
            $ulClass = ' class="tree"';
        } elseif ($isOpen) {
            $ulClass = ' class="open"';
        } else {
            $ulClass = '';
        }

        $html = '<ul' . $ulClass . '>';

        foreach ($nodes as $node) {
            $html .= '<li>';
            $html .= '<div class="tree-item">';

            if ($node->isDirectory) {
                // это не используется, но можно подключить
                $hasActiveChild = $this->hasActiveDescendant($node);
                $openClass = $hasActiveChild ? ' open' : '';

                $html .= '<button class="tree-toggle open">';
                $html .= $this->toggleSvg;
                $html .= '</button>';

                $folderLink = $this->baseUri . ($node->path ? strtolower($node->path) : '');
                $activeClass = $node->isActive ? ' active' : '';

                $html .= '<a href="' . $folderLink . '" class="tree-link' . $activeClass . '">' . htmlspecialchars($node->name) . '</a>';
                $html .= '</div>';

                if (!empty($node->children)) {
                    // можно передать статус активности потомков ($hasActiveChild) как флаг открытости $isOpen
                    $html .= $this->build($node->children, false, true);
                }
            } else {
                $html .= '<button class="tree-toggle" style="visibility: hidden;"></button>';

                if ($node->isExternal) {
                    $link = htmlspecialchars($node->path);
                    $target = ' target="_blank"';
                } else {
                    $link = $this->baseUri . htmlspecialchars(strtolower($node->path));
                    $target = '';
                }

                $activeClass = $node->isActive ? ' active' : '';
                $html .= '<a href="' . $link . '" class="tree-link' . $activeClass . '"' . $target . '>' . htmlspecialchars($node->name) . '</a>';
                $html .= '</div>';
            }

            $html .= '</li>';
        }

        $html .= '</ul>';
        return $html;
    }

    private function hasActiveDescendant(NodeDTO $node): bool
    {
        if ($node->isActive) {
            return true;
        }
        foreach ($node->children as $child) {
            if ($this->hasActiveDescendant($child)) {
                return true;
            }
        }
        return false;
    }
}
