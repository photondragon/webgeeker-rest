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

    private static $dbConfig;
    private static $dbs = [];
    private static $defaultDb = null; //默认的数据库（全局唯一）


    /**
     * 初始化所有数据库
     * @param $dbConfig array 数据库配置信息, 其中必须包含一个'default'的项做为默认数据库
     * @throws \Exception
     */
    public static function initDbs($dbConfig)
    {
        if(is_array($dbConfig)===false)
            throw new \Exception('Database::initDbs(): 参数$dbConfig必须是array');
        if(isset($dbConfig['default'])===false)
            throw new \Exception('Database::initDbs(): 参数$dbConfig无效, $dbConfig["default"]不存在');
        self::$dbConfig = $dbConfig;
    }

    /**
     * @param string $dbCfgName 数据库配置名
     * @param bool $restrict 是否是严格模式
     * @return null|Database 返回$dbCfgName指定的数据库对象. 如果不存在, 当$restrict为true时, 返回null; 否则返回'default'数据库对象
     */
    public static function getDb($dbCfgName = 'default', $restrict = false)
    {
        if(isset(self::$dbs[$dbCfgName])===false)
        {
            if(isset(self::$dbConfig[$dbCfgName])){
                $db = new Database(self::$dbConfig[$dbCfgName]);
                self::$dbs[$dbCfgName] = $db;
                return $db;
            } else {
                if($restrict)
                    return null;
                if (self::$defaultDb === null) {
                    self::$defaultDb = new Database(self::$dbConfig['default']);
                    self::$dbs['default'] = self::$defaultDb;
                }
                return self::$defaultDb;
            }
        }
        else
            return self::$dbs[$dbCfgName];
    }

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
            $dsn = "mysql:host={$dbhost};dbname={$dbname};charset=utf8mb4";
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

}