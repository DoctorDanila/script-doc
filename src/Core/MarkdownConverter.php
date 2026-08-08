<?php

namespace DoctorDanila\ScriptDoc\Core;

use cebe\markdown\GithubMarkdown;

class MarkdownConverter
{
    private GithubMarkdown $parser;

    public function __construct()
    {
        $this->parser           = new GithubMarkdown();
        $this->parser->html5    = true;
    }

    public function convert(string $markdownContent): string
    {
        return $this->parser->parse($markdownContent);
    }
}
