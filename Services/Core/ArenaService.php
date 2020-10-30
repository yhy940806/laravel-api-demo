<?php

namespace App\Services\Core;

use App\Contracts\Core\ArenaContract;

class ArenaService implements ArenaContract {

    const ALLOWED_ENV = ["prod", "staging", "develop"];

    const ENV_APPS = [
        "prod"    => "web",
        "staging" => "staging",
        "develop" => "develop",
    ];

    const CONFIG_NAME = "arena";
    /**
     * @var string
     */
    private string $env;

    public function __construct() {
        $this->env = app()->environment(self::ALLOWED_ENV) ? app()->environment() : "develop";
    }

    /**
     * @param string $appName
     * @return string|null
     */
    public function cloudUrl(string $appName): ?string {
        return config()->get($this->getAppVersionKey($appName) . ".aws.cloud.url");
    }

    /**
     * @param string $appName
     * @param string|null $default
     * @return string|null
     */
    public function appUrl(string $appName, ?string $default = null): ?string {
        return config()->get($this->getAppVersionKey($appName) . ".app.url", $default);
    }

    /**
     * @param string $appName
     * @return string
     */
    private function getAppVersionKey(string $appName): string {
        return $this->getAppPrefix($appName) . "." . self::ENV_APPS[$this->env];
    }

    /**
     * @param string $appName
     * @return string
     */
    private function getAppPrefix(string $appName): string {
        return self::CONFIG_NAME . "." . rtrim($appName, ".\t\n\r\0\x0B");
    }

    public function appVar(string $appName, string $appVarName, bool $isVersioning = false): ?string {
        $appKay = $isVersioning ? $this->getAppVersionKey($appName) : $this->getAppPrefix($appName);

        return config()->get($appKay . "." . $appVarName);
    }
}