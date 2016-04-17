<?php
/*
 * Project: study
 * File: Auth.php
 * CreateTime: 16/2/3 00:44
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file Auth.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Rest;

use \WebGeeker\Utils\SimpleCookie;

/**
 * @class Auth
 * @brief brief description
 *
 * elaborate description
 */
class Auth
{
    const key = '$6#@2G@b9#$e#$Aa';
    const encrypt = false;

    public static $loginValidityPeriod = 3600*24*7; //登录有效期，单位秒

    /**
     * 检测当前是否有用户登录
     * @return int 返回当前登录用户的ID
     * @throws \Exception 如果没有登录，会抛出异常
     */
    public static function checkLoginedUid()
    {
//        var_export(SimpleCookie::getCookies());
        $sessionKey = SimpleCookie::get('sessionKey');
        $sessionKey = self::decrypt($sessionKey);
        $info = explode(',', $sessionKey);
        if (!(count($info)===2))
            throw new \Exception('未登录');
        $id = $info[0];
        $expire = $info[1];
        if(time()<$expire)
            return (int)$id;
        throw new \Exception('未登录');
    }

    /**
     * @return int 返回当前登录用户的ID，如果没有登录，返回0
     */
    public static function getLoginedUid()
    {
        try {
            $id = self::checkLoginedUid();
            return $id;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 标记为已登录
     * @param $uid int|string 登录用户ID
     */
    public static function markAsLogin($uid)
    {
        $sessionKey = "$uid," . (time()+self::$loginValidityPeriod);
        $secretToken = "$uid," . (time()+self::$loginValidityPeriod);
        $sessionKey = self::encrypt($sessionKey);
        $secretToken = self::encrypt($secretToken);
        SimpleCookie::set('sessionKey', $sessionKey, self::$loginValidityPeriod+60);
        SimpleCookie::set('secretToken', $secretToken, self::$loginValidityPeriod+60, true);
    }

    /**
     * 标记为已登出
     */
    public static function markAsLogout()
    {
        // 设置一个无效的token，因为PostMan似乎不能正确删除cookie (最终发现这个问题是因为cookie的path设置不一致造成)
//        SimpleCookie::set('sessionKey', "0", self::$loginValidityPeriod+60);
//        SimpleCookie::set('secretToken', "0", self::$loginValidityPeriod+60, true);
        SimpleCookie::remove('sessionKey');
        SimpleCookie::remove('secretToken', true);
    }

    public static function checkHttps()
    {
        if(@$_SERVER['HTTPS'] !== 'on') {
            throw new \Exception('只接受https安全连接');
        }
    }

    public static function checkLoginedUidSecretly()
    {
        $secretToken = SimpleCookie::get('secretToken');
        $secretToken = self::decrypt($secretToken);
        $info = explode(',', $secretToken);
        if (!(count($info)===2))
            throw new \Exception('未登录');
        $id = $info[0];
        $expire = $info[1];
        if(time()>=$expire)
            throw new \Exception('已过期，请重新登录');
        return $id;
    }

    public static function getLoginedUidSecretly()
    {
        try {
            $id = self::checkLoginedUidSecretly();
            return $id;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function updateToken()
    {
        $id = self::checkLoginedUidSecretly();
        self::markAsLogin($id);
    }

    static public function encrypt($data) {
        if(self::encrypt!==true)
            return $data;
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, self::key, $data, MCRYPT_MODE_ECB);
        return base64_encode($encrypted);
    }

    static public function decrypt($data) {
        if(self::encrypt!==true)
            return $data;
        $data = base64_decode($data);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, self::key, $data, MCRYPT_MODE_ECB);
        return rtrim($decrypted,"\0");
    }

}