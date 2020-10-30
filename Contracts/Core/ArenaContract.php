<?php

namespace App\Contracts\Core;

interface ArenaContract {
    public function cloudUrl(string $appName): ?string;

    public function appUrl(string $appName, ?string $default = null): ?string;

    public function appVar(string $appName, string $appVarName, bool $isVersioning = false): ?string;
}