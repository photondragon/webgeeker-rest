<?php
/*
 * Project: simpleim
 * File: Database.php
 * CreateTime: 16/3/23 20:40
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file Database.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Rest;

/**
 * @class Database
 * @package WebGeeker\Rest
 * @brief 代表一个数据库连接（或数据库实例）
 *
 * elaborate description
 */
class Database
{
    public $pdo;

    public function __construct($dbParams)
    {
        if(count($dbParams)==0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有提供数据库连接参数");
        $dbhost = $dbParams['dbhost'];
        $dbname = $dbParams['dbname'];
        $dbuser = $dbParams['dbuser'];
        $dbpass = $dbParams['dbpass'];
        $dsn = "mysql:host={$dbhost};dbname={$dbname};charset=utf8";
        $this->pdo = new \PDO($dsn, $dbuser, $dbpass);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //出错就抛异常
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); //防止数字变字符串
        $this->pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false); //防止数字变字符串。不过这
    }

    private static $defaultDB = null; //默认的数据库（全局唯一）

    /**
     * @return null|Database
     */
    public static function getDefaultDB() //获取默认的数据库（全局唯一），默认值是null
    {
        return self::$defaultDB;
    }

    /**
     * 设置默认的数据库
     * 可以在程序初始化的时候设置。
     * @param Database $database
     */
    public static function setDefaultDB(Database $database)
    {
        self::$defaultDB = $database;
    }

    /**
     * @param $sql
     * @return int 返回受影响的行数
     * @throws \Exception
     */
    public function executeSql($sql)
    {
        $ret = $this->pdo->exec($sql);
        if($ret===false) //如果配置PDO为抛出异常，则不会返回false
        {
            $this->pdo->errorInfo();
            throw new \Exception($this->pdo->errorInfo());
        }
        return $ret;
    }
}