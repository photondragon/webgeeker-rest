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
    const encrypt = true;

    //长期Token一般用于非敏感信息的获取
    //短期Token一般用于信息的修改和敏感信息的获取
    public static $longTermTokenDuration = 3600*24*7; //读Token有效期，单位秒（7天）
    public static $shortTermTokenDuration = 900; //写Token有效期，单位秒（15分钟）

    /**
     * 检测当前是否有用户登录（并且其长期Token有效）
     * @return int 返回当前登录用户的ID
     * @throws \Exception 如果没有登录或长期Token失效，会抛出异常
     */
    public static function checkLoginUid()
    {
        $sessionKey = SimpleCookie::get('session');
        $sessionKey = self::decrypt($sessionKey);
        $info = explode(',', $sessionKey);
        if (count($info)<3)
            throw new \Exception('未登录');
        $id = $info[0];
        $expire = $info[1];
        if(time()<$expire)
            return (int)$id;
        throw new \Exception('未登录');
    }

    /**
     * 检测当前是否有用户登录（并且其短期Token有效）
     * @return int 返回当前登录用户的ID
     * @throws \Exception 如果没有登录或短期Token失效，会抛出异常
     */
    public static function checkLoginUidWithShortToken()
    {
        $sessionKey = SimpleCookie::get('session');
        $sessionKey = self::decrypt($sessionKey);
        $info = explode(',', $sessionKey);
        if (count($info)<3)
            throw new \Exception('未登录');
        $id = $info[0];
        $expire = $info[1];
        if(time()<$expire)
            return (int)$id;
        throw new \Exception('未登录');
    }

    /**
     * @return int 返回当前已登录（并且其长期Token有效的）用户的ID; 否则返回0
     */
    public static function getLoginUid()
    {
        try {
            $id = self::checkLoginUid();
            return $id;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * @return int 返回当前已登录（并且其短期Token有效的）用户的ID; 否则返回0
     */
    public static function getLoginUidWithShortToken()
    {
        try {
            $id = self::checkLoginUidWithShortToken();
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
        $now = time();
        $sessionKey = "$uid," . ($now+self::$longTermTokenDuration) . ',' . ($now+self::$shortTermTokenDuration) . ',' . rand(1000, 99999999);
        $secretToken = "$uid," . ($now+self::$longTermTokenDuration) . ',' . rand(1000, 99999999);
        $sessionKey = self::encrypt($sessionKey);
        $secretToken = self::encrypt($secretToken);
        SimpleCookie::set('session', $sessionKey, self::$longTermTokenDuration+60);
        SimpleCookie::set('token', $secretToken, self::$longTermTokenDuration+60, true);
    }

    /**
     * 标记为已登出
     */
    public static function markAsLogout()
    {
        SimpleCookie::remove('session');
        SimpleCookie::remove('token', true);

        SimpleCookie::remove('sessionKey');
        SimpleCookie::remove('secretToken', true);
        SimpleCookie::remove('secret', true);
    }

    public static function checkHttps()
    {
        if(@$_SERVER['HTTPS'] !== 'on') {
            throw new \Exception('只接受https安全连接');
        }
    }

    public static function checkLoginUidSecretly()
    {
        $secretToken = SimpleCookie::get('token');
        $secretToken = self::decrypt($secretToken);
        $info = explode(',', $secretToken);
        if (count($info)<2)
            throw new \Exception('未登录');
        $id = $info[0];
        $expire = $info[1];
        if(time()>=$expire)
            throw new \Exception('已过期，请重新登录');
        return $id;
    }

    public static function getLoginUidSecretly()
    {
        try {
            $id = self::checkLoginUidSecretly();
            return $id;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function renewToken()
    {
        $id = self::checkLoginUidSecretly();
        self::markAsLogin($id);
    }

    static private function encrypt($data) {
        if(self::encrypt!==true)
            return $data;
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, self::key, $data, MCRYPT_MODE_ECB);
        return base64_encode($encrypted);
    }

    static private function decrypt($data) {
        if(self::encrypt!==true)
            return $data;
        $data = base64_decode($data);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, self::key, $data, MCRYPT_MODE_ECB);
        return rtrim($decrypted,"\0");
    }

    /**
     * 异或加密/解密
     * 加密: 明文 ^ 密钥 = 密文
     * 加密: 密文 ^ 密钥 = 明文
     * 在已知明文的情况下, 异或加密是脆弱的, 因为: 明文 ^ 密文 = 密钥
     * 提高安全性
     * 1. 密钥长度等于待加密字符串
     * 2. 若key用伪随机算法生成, 结果就是流密码
     * 3. 若key是真正随机的, 结果就是一次性密码本, 这种密码在理论不可破解
     * @param $string string 明文
     * @param string $key 密钥
     * @return string 密文
     */
    static function encryptXor($string, $key = '') {
        if(strlen($string)===0)
            return '';
        if(strlen($key)===0)
            $key = 'dfsfasfasjhk';
        $len1 = strlen($string);
        $len2 = strlen($key);
        if($len1 > $len2) // 密钥短于明文
            $key = str_repeat($key, ceil($len1 / $len2));
        return $string ^ $key;
    }
}