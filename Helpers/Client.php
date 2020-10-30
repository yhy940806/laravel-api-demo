<?php

namespace App\Helpers;

use App\Facades\Cache\AppCache;
use App\Models\Core\Auth\AuthModel;
use App\Models\Core\App;
use Exception;
use Config;

class Client {
    /**
     * @return App|null
     */
    public static function app(): ?App {
        return (Config::get("global.app"));
    }

    public static function platform(): string {
        return (Config::get("global.platform"));
    }

    /**
     * @return AuthModel|null
     */
    public static function auth(): ?AuthModel {
        return (Config::get("global.auth"));
    }

    /**
     * @return mixed
     */
    public static function browser() {
        return (Config::get("global.client.browser"));
    }

    /**
     * @param string $apiHost
     * @return void
     */
    public static function checkingAs(string $apiHost = "app.arena.soundblock.web") {
        $_SERVER["HTTP_X_API"] = "v1.0";
        if (!static::validateHostHeader($apiHost))
            throw new Exception("HTTP_X_API_HOST exception", 400);
        $_SERVER["HTTP_X_API_HOST"] = $apiHost;
    }

    /**
     * @param string $header
     * @return bool
     */
    public static function validateHostHeader(string $header): bool {
        $cacheAuthKey = self::class . "validateHostHeader.Headers.{$header}.Auth";
        $cacheAppKey = self::class . "validateHostHeader.Headers.{$header}.App";

        $headerPattern = "/\A(\bapp\b).(\barena\b).[a-zA-Z]+.(\bweb\b)|(\bandroid\b)|(\bios\b)/i";

        if (!preg_match($headerPattern, $header)) {
            return (false);
        }

        $headerToken = explode(".", $header);
        $app = $headerToken[2];
        $platform = Util::ucfLabel($headerToken[3]);

        $authName = "App." . Util::ucfLabel($app);

        if (AppCache::isCached($cacheAuthKey)) {
            $objAuth = AppCache::getCache($cacheAuthKey);
        } else {
            $objAuth = AuthModel::where("auth_name", $authName)->first();
            AppCache::setCache($objAuth, $cacheAuthKey);
        }

        if (AppCache::isCached($cacheAppKey)) {
            $objApp = AppCache::getCache($cacheAppKey);
        } else {
            $objApp = App::where("app_name", Util::lowerLabel($app))->first();
            AppCache::setCache($objApp, $cacheAppKey);
        }

        if ($objApp) {
            Config::set("global.app", $objApp);
            Config::set("global.platform", $platform);
            Config::set("global.auth", $objAuth);

            return (true);
        } else {
            return (false);
        }
    }

    /**
     * @param string $header
     * @return bool
     */
    public static function validateHeader(string $header): bool {
        if (Util::lowerLabel($header) === "v1.0") {
            return (true);
        } else {
            return (false);
        }
    }
}
