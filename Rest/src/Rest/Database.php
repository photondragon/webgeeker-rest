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
    const DBTypeMysql = 0;
    const DBTypeMongoDB = 1;
    const DBTypeSqlite = 2;

    public $dbType;
    public $pdo;

    public function __construct($dbParams)
    {
        if(count($dbParams)==0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 没有提供数据库连接参数");
        $dbtype = @$dbParams['dbtype'];
        if($dbtype==='mysql') {
            $this->dbType = self::DBTypeMysql;
            $dbhost = $dbParams['dbhost'];
            $dbname = $dbParams['dbname'];
            $dbuser = $dbParams['dbuser'];
            $dbpass = $dbParams['dbpass'];
            $dsn = "mysql:host={$dbhost};dbname={$dbname};charset=utf8";
            $this->pdo = new \PDO($dsn, $dbuser, $dbpass);
        }
        else if($dbtype==='sqlite'){
            $this->dbType = self::DBTypeSqlite;
            $dbfile = @$dbParams['dbfile'];
            $this->pdo = new \PDO("sqlite:$dbfile");
        }
        else
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 不支持的数据库类型");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //出错就抛异常
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); //防止数字变字符串
        $this->pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false); //防止数字变字符串。不过这属性默认为false
    }

    /**
     * @param $sql
     * @return int 返回受影响的行数
     * @throws \Exception
     */
    public function executeSql($sql)
    {
        $ret = $this->pdo->exec($sql);
        if($ret===false) //如果配置PDO为抛出异常，则不会返回false.
            throw new \Exception($this->pdo->errorInfo());
        return $ret;
    }

    private static $defaultDb = null; //默认的数据库（全局唯一）

//    /**
//     * @return null|Database
//     */
//    public static function getDefaultDb() //获取默认的数据库（全局唯一），默认值是null
//    {
//        return self::$defaultDb;
//    }

    /**
     * 设置默认的数据库
     * 可以在程序初始化的时候设置。
     * @param Database $database
     */
    public static function setDefaultDb(Database $database)
    {
        self::$defaultDb = $database;
    }

    private static $dbs = [];

    /**
     * 配置表对应的数据库
     * @param Database $db
     * @param $tableName string
     * @throws \Exception
     */
    public static function setDbForTable(Database $db, $tableName)
    {
        if(strlen($tableName)===0)
            throw new \Exception('Database::' . __FUNCTION__ . '(): 无效的参数$tableName');
        if($db instanceof self === false)
            throw new \Exception('Database::' . __FUNCTION__ . '(): 无效的参数$db');
        self::$dbs[$tableName] = $db;
    }

    /**
     * 根据表名获取对应的数据库, 如果没有配置, 则返回默认数据库
     * @param $tableName string
     * @return Database|null
     */
    public static function getDbForTable($tableName)
    {
        $db = @self::$dbs[$tableName];
        if($db===null)
            return self::$defaultDb;
        return $db;
    }

}