<?php
/*
 * Project: study
 * File: Cookie.php
 * CreateTime: 16/2/2 20:41
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file SimpleCookie.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Utils;

/**
 * @class SimpleCookie
 * @brief brief description
 *
 * elaborate description
 */
class SimpleCookie
{
    public static function get($name)
    {
        return @$_COOKIE[$name];
    }

    public static function getCookies()
    {
        return $_COOKIE;
    }

    /**
     * @param $name
     * @param $value
     * @param int $duration 有效时间，单位秒
     * @param bool|false $httpsOnly 只针对https连接设置
     * @param bool|false $hideFromJS 对客户端js隐藏，可以有效降低XSS攻击
     */
    public static function set($name, $value, $duration, $httpsOnly = false, $hideFromJS = true)
    {
        setcookie($name, $value, time()+$duration, null, null, $httpsOnly, $hideFromJS);
    }

    /**
     * 设置只在会话期间有效的Cookie，当用户关闭浏览器后自动失效。
     * @param $name
     * @param $value
     * @param bool|false $httpsOnly 只针对https连接设置
     * @param bool|false $hideFromJS 对客户端js隐藏，可以有效降低XSS攻击
     */
    public static function setWhenSession($name, $value, $httpsOnly = false, $hideFromJS = true)
    {
        setcookie($name, $value, 0, null, null, $httpsOnly, $hideFromJS);
    }

    public static function remove($name, $httpsOnly = false, $hideFromJS = true)
    {
        setcookie($name, $_COOKIE[$name], 1, null, null, $httpsOnly, $hideFromJS);
    }

}