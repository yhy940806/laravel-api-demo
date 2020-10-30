<?php

namespace App\Helpers;

use Exception;

class Builder
{

    /**
     * @param mixed
     * @return string
     */
    public static function notification_button($items)
    {
        $items = is_array($items) ? $items : func_get_args();
        $action = "";
        foreach ($items as $item)
        {
            $action .= static::build_action_button($item);
        }

        return($action);
    }

    /**
     * @param array
     * @return string
     * @throws Exception
     */
    public static function notification_link(array $item)
    {
        $action = "";
        if (!is_array($item) || !Util::array_keys_exists(["url", "link_name"], $item))
            throw new Exception("Invalid Parameter", 400);
        $action .= static::build_action_link($item["link_name"], $item["url"]);

        return($action);
    }

    /**
     * @param string $strBtnName
     * @param string $strClassName
     * @return string
     */
    protected static function build_action_button(string $strBtnName, ?string $strClassName = "arena-notification-button")
    {
        $strBtnName = strtoupper($strBtnName);
        return("<button class='{$strClassName}'><span class='arena-notification-span'>{$strBtnName}</span></button>");
    }

    /**
     * @param string $strLinkName
     * @param string $linkUrl
     * @param string $strClassName
     * @return string
     */
    protected static function build_action_link(string $strLinkName, string $linkUrl, ?string $strClassName = "arena-notification-button")
    {
        return("<a href='{$linkUrl}' class='{$strClassName}'><span class='arena-notification-span'>{$strLinkName}</span></a>");
    }
}
