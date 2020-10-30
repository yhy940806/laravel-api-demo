<?php

if ( !function_exists("cloud_url") ) {
    function cloud_url(string $appName) {
        return app()->make("arena")->cloudUrl($appName);
    }
}

if ( !function_exists("app_url") ) {
    function app_url(string $appName, ?string $default = null) : ?string {
        return app()->make("arena")->appUrl($appName, $default);
    }
}

if ( !function_exists("app_var") ) {
    function app_var(string $appName, string $appVarName, bool $isVersioning = false): ?string {
        return app()->make("arena")->appVar($appName, $appVarName, $isVersioning);
    }
}