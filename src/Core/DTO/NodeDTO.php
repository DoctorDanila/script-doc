<?php

namespace DoctorDanila\ScriptDoc\Core\DTO;

class NodeDTO
{
    public string $name;
    public string $path;
    public bool $isDirectory;
    public bool $isExternal;
    public bool $isActive   = false;
    public bool $hasReadme  = false;
    public bool $isRoot     = false;

    /** @var NodeDTO[] */
    public array $children = [];

    public function __construct(
        string $name,
        string $path,
        bool $isDirectory   = false,
        bool $isExternal    = false
    ) {
        $this->name         = $name;
        $this->path         = $path;
        $this->isDirectory  = $isDirectory;
        $this->isExternal   = $isExternal;
    }
}
