<?php

namespace DoctorDanila\ScriptDoc\Core;

use DoctorDanila\ScriptDoc\Core\DTO\NodeDTO;

class Scanner
{
    public function readFile(string $path): ?string
    {
        return (file_exists($path) && is_file($path)) ? file_get_contents($path) : null;
    }
}
