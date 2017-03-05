<?php
/*
 * Project: study
 * File: Table.php
 * CreateTime: 16/1/22 02:59
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file Table.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Rest;

/**
 * @class Table
 * @brief 代表一张表
 *
 * elaborate description
 */

class Table //implements ArrayAccess
{
    private static $modelDbMappings = [];
    private static $tables = [];

    /**
     * 初始化model-db的映射关系
     * @param $modelDbMappings
     */
    public static function initWithModelDbMappings(array $modelDbMappings)
    {
        self::$modelDbMappings = $modelDbMappings;
    }

    /**
     * 根据模型名称获取对应的Table
     * @param $modelName string
     * @return Table
     */
    public static function getTableForModel($modelName)
    {
        if (isset(self::$tables[$modelName])==false) {
            $mapping = @self::$modelDbMappings[$modelName];
            self::$tables[$modelName] = new Table($modelName, $mapping);
        }
        return self::$tables[$modelName];
    }

    protected $tableName; //表名。mysql表名字段名都是忽略大小写的
    protected $primaryKey; //主键
    protected $uniques;  //唯一索引数组（视为主键）

    const MappingTypeNormal = 0;    // 不分片
    const MappingTypeSharding = 1;  // 分片
    protected $mappingType;

    protected $shardType;

    protected $shardKey;
    protected $shardSetp;  // 每个分片的长度
    /** @var $shards Shard[] 分片的数组 */
    protected $shards;
    protected $shardsCount;  //分片数
    protected $shardsFilledCount;  // 已满的分片个数

    /**
     * Table constructor.
     * @param $tableName string 表名
     * @param $mapping null|array model-db映射信息
     * @throws \Exception
     */
    public function __construct($tableName, $mapping = null)
    {
        $this->tableName = $tableName;
        if(is_array($mapping)===false) //使用默认数据库,不分片
        {
            $mapping = [
                'mappingType' => 'normal',
                'db' => 'default',
            ];
        }

        if(isset($mapping['mappingType'])===false)
            throw new \Exception("配置错误: 表${tableName}映射信息缺少mappingType字段");

        if($mapping['mappingType'] === 'sharding') {
            $this->mappingType = self::MappingTypeSharding;

            if(isset($mapping['sharding'])===false)
                throw new \Exception("配置错误: 表${tableName}映射信息缺少sharding字段");

            $sharding = $mapping['sharding'];

            $shardKey = @$sharding['shardKey'];
            $shardType = @$sharding['shardType'];
            $shardSetp = (int)@$sharding['shardSetp'];
            $shardInfos = @$sharding['shards'];
            $shardsFilledCount = (int)@$sharding['shardsFilledCount'];
            if(strlen($shardKey)===0)
                throw new \Exception("配置错误: 表${tableName}分片信息的shardKey字段无效");
            if($shardType !== 'range')
                throw new \Exception("配置错误: 表${tableName}分片方式必须为'range'");
            if($shardSetp < 10 || $shardSetp > 20000000)
                throw new \Exception("配置错误: 表${tableName}每个分片的容量必须在[10, 20000000]之间");
            if(is_array($shardInfos)===false ||  count($shardInfos) === 0)
                throw new \Exception("配置错误: 表${tableName}分片信息中的shards字段无效");

            $shardsCount = 0;
            $shards = [];
            foreach ($shardInfos as $shardInfo) {
                $dbName = @$shardInfo['db'];
                $tbName = @$shardInfo['table'];
                if(strlen($dbName)===0)
                    throw new \Exception("配置错误: 表${tableName}分片${shardsCount}的信息无效");
                $db = Database::getDb($dbName, true);
                if($db===null)
                    throw new \Exception("配置错误: 表${tableName}分片${shardsCount}的数据库信息不存在");

                if(strlen($tbName)===0){
                    if($shardsCount === 0)
                        $tbName = $tableName;
                    else
                        $tbName = "${tableName}__$shardsCount";
                }
                $shard = new Shard($db, $tbName);
                $shards[] = $shard;
                $shardsCount++;
            }
            if($shardsFilledCount<0)
                $shardsFilledCount = 0;
            else if($shardsFilledCount > $shardsCount)
                $shardsFilledCount = $shardsCount;

            $this->shardType = $shardType;

            $this->shardKey = $shardKey;
            $this->shardSetp = $shardSetp;
            $this->shards = $shards;
            $this->shardsCount = $shardsCount;
            $this->shardsFilledCount = $shardsFilledCount;

        } else {
            $this->mappingType = self::MappingTypeNormal;

            $db = Database::getDb(@$mapping['db']);
            $shard = new Shard($db, $tableName);
            $this->shardsCount = 1;
            $this->shards = [$shard];
        }
    }

    /**
     * @return int MappingTypeNormal | MappingTypeSharding
     */
    public function getMappingType(){
        return $this->mappingType;
    }
    /**
     * @return Shard[]
     */
    public function getShards()
    {
        return [] + $this->shards;
    }

    public function getShardStep()
    {
        return $this->shardSetp;
    }

    /**
     * 如果$fieldName等于片键, 则只会返回一个shard, 否则返回所有shards
     * @param $fieldName
     * @param $value
     * @return array|Shard[]
     * @throws \Exception
     */
    protected function shardsWithFieldValue($fieldName, $value)
    {
        if($fieldName === $this->shardKey){
            $i = (int)((int)$value / $this->shardSetp);
            if(isset($this->shards[$i])===false)
                throw new \Exception("找不到表{$this->tableName}的分片$i");
            return [$this->shards[$i]];
        }
        else{
            return $this->shards;
        }
    }

    /**
     * 如果fields包含片键, 则只会返回一个shard, 否则返回所有shards
     * @param $fields
     * @return array|Shard[]
     * @throws \Exception
     */
    protected function shardsWithFields($fields)
    {
        if(isset($fields[$this->shardKey])){
            $value = $fields[$this->shardKey];
            $i = (int)((int)$value / $this->shardSetp);
            if(isset($this->shards[$i])===false)
                throw new \Exception("找不到表{$this->tableName}的分片$i");
            return [$this->shards[$i]];
        }
        return $this->shards;
    }

    /**
     * 根据字段值检测记录是否存在
     * @param $fieldName
     * @param $value
     * @return bool
     * @throws \Exception
     */
    public function existsWithFieldValue($fieldName, $value)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->existsWithFieldValue($fieldName, $value);

        if (strlen($fieldName)==0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 参数fieldName无效");

        $shards = $this->shardsWithFieldValue($fieldName, $value);

        foreach ($shards as $shard) {
            if($shard->existsWithFieldValue($fieldName, $value))
                return true;
        }

        return false;
    }

    /**
     * @param $fieldName
     * @param $value
     * @return null|array
     * @throws \Exception
     */
    public function findByField($fieldName, $value)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->findByField($fieldName, $value);

        if (strlen($fieldName)==0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 参数fieldName无效");

        $shards = $this->shardsWithFieldValue($fieldName, $value);

        foreach ($shards as $shard) {
            $info = $shard->findByField($fieldName, $value);
            if($info !== null)
                return $info;
        }

        return null;
    }

    /**
     * 相当于 SELECT * WHERE field IN(?,?,?) 语句
     * @param $fieldName string
     * @param $valueList array
     * @return array[] 如果fieldName是主键,并且片键也是主键, 则返回结果的顺序是有保障的; 否则返回结果的顺序是随机的.
     * @throws \Exception
     */
    public function findAllInFieldValueList($fieldName, $valueList)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->findAllInFieldValueList($fieldName, $valueList);

        if (strlen($fieldName) == 0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 参数fieldName无效");

        $count = count($valueList);
        if ($count === 0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $infos = [];
        if ($fieldName === $this->shardKey) {
            $map = [];
            foreach ($valueList as $value) {
                $i = (int)((int)$value / $this->shardSetp);
                if (isset($this->shards[$i]) === false)
                    continue;
                if (isset($map[$i]))
                    $map[$i][] = $value;
                else
                    $map[$i] = [$value];
            }

            foreach ($map as $i => $values) {
                $shard = $this->shards[$i];
                $array = $shard->findAllInFieldValueList($fieldName, $values);
                $infos = array_merge($infos, $array);
            }

        } else {
            foreach ($this->shards as $shard) {
                $array = $shard->findAllInFieldValueList($fieldName, $valueList);
                $infos = array_merge($infos, $array);
            }

        }

        if ($fieldName !== $this->primaryKey)  // 查询的不是主键
            return $infos;  // 直接返回未排序的结果

        // 如果查询的是主键列表
        // 根据查询的顺序对结果重新排序, 查询不到的位置设为null
        $returns = [];
        foreach ($valueList as $value) {
            $found = false;
            foreach ($infos as $info) {
                if ($info->$fieldName === $value) {
                    $returns[] = $info;
                    $found = true;
                    break;
                }
            }
            if ($found === false)
                $returns[] = null;
        }
        return $returns;
    }

    /**
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @return null|array
     * @throws \Exception
     */
    public function findByFields($fields)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->findByFields($fields);

        if (is_array($fields)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $shards = $this->shardsWithFields($fields);

        foreach ($shards as $shard) {
            $info = $shard->findByFields($fields);
            if($info !== null)
                return $info;
        }

        return null;
    }

    /**
     * 即使有多条记录匹配, 也只查询和修改一条记录
     * 一般用于计数器的增减之类的操作（对幂等性没有要求的）
     * 钱的增减之类的操作不可使用此函数
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @param $increaseField string 要增加的字段名
     * @param $deltaValue int 要增加的数值, 可正可负
     * @return null|array
     * @throws \Exception
     */
    public function findAndIncreaseByFields($fields, $increaseField, $deltaValue)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->findAndIncreaseByFields($fields, $increaseField, $deltaValue);

        if (is_array($fields)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $shards = $this->shardsWithFields($fields);

        foreach ($shards as $shard) {
            $info = $shard->findAndIncreaseByFields($fields, $increaseField, $deltaValue);
            if($info !== null)
                return $info;
        }

        return null;
    }

    /**
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @return array[]
     * @throws \Exception
     */
    public function findAllByFields($fields)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->findAllByFields($fields);

        if (is_array($fields)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $shards = $this->shardsWithFields($fields);

        $infos = [];
        foreach ($shards as $shard) {
            $array = $shard->findAllByFields($fields);
            if($array !== null)
                $infos = array_merge($infos, $array);
        }

        return $infos;
    }

    /**
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @return int
     * @throws \Exception
     */
    public function countByFields($fields)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->countByFields($fields);

        if (is_array($fields)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $shards = $this->shardsWithFields($fields);

        $count = 0;
        foreach ($shards as $shard) {
            $count += $shard->countByFields($fields);
        }

        return $count;
    }

    /**
     * @param IQuery $query
     * @return array[]
     */
    public function findAllWithQuery(IQuery $query)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->findAllWithQuery($query);

        // todo 目前实现有问题, 可能出现内存占用过高导致崩溃, 而且也没做shards过滤,所有shards都要查一遍
        $offset = $query->getOffset();
        $count = $query->getCount();

        $maxlimit = 100000;
        if($offset>0){
            if($count===0)
                $query->segment(0,0);
            else
                $query->segment($count+$offset, 0);
            $maxlimit += $offset;
        }

        $orderBys = $query->getSortFields();
        $needsSort = count($orderBys) > 0;
        if($needsSort) {
            // 生成排序函数
            $sortfunc = function ($x, $y) use ($orderBys) {
                foreach ($orderBys as $orderBy) {
                    $order = $orderBy[1];
                    if ($order === IQuery::OrderNone)
                        continue;
                    $fieldName = $orderBy[0];
                    $val1 = $x->$fieldName;
                    $val2 = $y->$fieldName;
                    if ($order === IQuery::OrderAsc) {
                        if ($val1 < $val2)
                            return -1;
                        elseif ($val1 > $val2)
                            return 1;
                    } else {
                        if ($val1 < $val2)
                            return 1;
                        elseif ($val1 > $val2)
                            return -1;
                    }
                }
                return 0;
            };
        }

        $infos = [];
        $sorted = true;
        foreach ($this->shards as $shard) {
            $array = $shard->findAllWithQuery($query);

            $infos = array_merge($infos, $array);
            $sorted = false;

            if(count($infos)>$maxlimit) //大于10万条记录, 排序后截断
            {
                if($needsSort){
                    usort($infos, $sortfunc);
                    $sorted = true;
                }
                $infos = array_slice($infos, 0, $maxlimit);
            }
        }
        if($needsSort){
            if($sorted===false)
            {
                usort($infos, $sortfunc);
            }
        }
        return array_slice($infos, $offset, $count);
    }

    /**
     * 生成用于检测记录是否存在的WHERE子句（根据唯一索引）
     * @param $fields
     * @param $uniques
     * @return string
     * @throws \Exception
     */
    private static function whereClauseForUniquesCheck($fields, $uniques)
    {
        $wheres = '';
        foreach ($uniques as $unique) {
            if (count($unique) === 1) // 单列唯一索引
            {
                $fieldName = $unique[0];
                if (isset($fields[$fieldName])) {
                    $value = $fields[$fieldName];
                    $col = MysqlQuery::sqlEscapeColumnName($fieldName);
                    $val = MysqlQuery::sqlEscapeValueForWhereClause($value);
                    if ($wheres)
                        $wheres .= " OR ($col=$val)";
                    else
                        $wheres = "($col=$val)";
                }
            } else  // 多列唯一索引
            {
                $cond = '';
                $needchk = false;
                foreach ($unique as $fieldName) {
                    $value = @$fields[$fieldName];

                    $col = MysqlQuery::sqlEscapeColumnName($fieldName);
                    $val = MysqlQuery::sqlEscapeValueForWhereClause($value);
                    if ($cond)
                        $cond .= " AND $col=$val";
                    else
                        $cond = "$col=$val";

                    if ($value !== null)
                        $needchk = true;
                }
                if ($needchk) //多列唯一索引中至少有一个字段是有value, 需要检测唯一性.（如果所有字段都是null, 则无需检测）
                {
                    if ($wheres)
                        $wheres .= " OR ($cond)";
                    else
                        $wheres = "($cond)";
                }
            }
        } // end foreach ($uniques as $unique)
        return $wheres;
    }

    /**
     * @param array $fields 要保存的字段，格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @param $autoIncrementKey string 表的自增字段
     * @param $uniques array[] 表的所有唯一索引,包括主键和联合主键
     * @return string 插入的ID
     * @throws \Exception
     */
    function insert(array $fields, $autoIncrementKey = '', $uniques = [])
    {
        if ($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->insert($fields);

        // 检查所有唯一索引, 以免并发的情况下重复插入多个分片
        if (is_array($uniques) && count($uniques) > 0) {
            $wheres = self::whereClauseForUniquesCheck($fields, $uniques);
            if ($wheres) // 需要到所有分片中检测记录是否已存在（根据唯一索引）
            {
                // todo 锁定待插入的数值, 以免多个客户端同时插入相同的数据（比如注册同一用户名）

                $wheres = "WHERE $wheres";
                foreach ($this->shards as $shard) {
                    $count = $shard->countWithWhereClause($wheres);
                    if ($count > 0) // 记录已存在
                    {
                        throw new \Exception("插入失败: 该记录已存在");
                    }
                }
            }
        } // end 检查所有唯一索引

        //如果提供了片键的值, 则直接定位到shard
        if (isset($fields[$this->shardKey])) {
            $value = $fields[$this->shardKey];
            $shards = $this->shardsWithFieldValue($this->shardKey, $value); //只会返回一个shard
            return $shards[0]->insert($fields);
        }

        // 以下是没有提供片键的值的情况

        if ($this->shardKey !== $autoIncrementKey)
            throw new \Exception("表{$this->tableName}启用了分片, 新插入的数据没有指定片键{$this->shardKey}的值, 并且片键字段不是auto increment（不能自动生成）, 所以无法确定新数据要插入的分片");

        // 以下是涉及到autoincrement的insert操作, 片键只会是整型, 不可能是字符串型

        $shardSetp = $this->shardSetp;
        $gapLen = $shardSetp / 10; //step gap用于指定两个分片之间保留的一段auto increment id
        if ($gapLen > 4000) $gapLen = 4000;
        elseif ($gapLen < 1) $gapLen = 1;

        for ($i = $this->shardsFilledCount; $i < $this->shardsCount; $i++) {
            $shard = $this->shards[$i];
            $shardEnd = ($i + 1) * $shardSetp;

            // 寻找要插入的分片
            $autoIncrement = $shard->getAutoIncrement(); // 查询当前分片
            if ($autoIncrement >= $shardEnd - $gapLen) //当autoIncrement接近分片的上限时, 转到下一个分片,以免在高并发情况下出现insert数据超过分片上限的情况
                continue;

            $insert_id = $shard->insert($fields);
            if ($insert_id >= $shardEnd) // 插入的记录超过分片上限了
            {
                $shard->deleteByField($autoIncrementKey, $insert_id);
                continue;
            }
            return $insert_id;
        }

        throw new \Exception("表{$this->tableName}的所有分片已满, 无法插入");
    }

    /**
     * @param array $fields 要保存的字段，格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @param $autoIncrementKey string 表的自增字段
     * @param $uniques array[] 表的所有唯一索引,包括主键和联合主键
     * @return string 插入的ID
     * @throws \Exception
     */
    function insertOrReplace(array $fields, $autoIncrementKey = '', $uniques = [])
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->insertOrReplace($fields);

        // 检查所有唯一索引, 以免并发的情况下重复插入多个分片
        $uniquesCount = count($uniques);
        if(is_array($uniques) && $uniquesCount>0) // 存在唯一索引
        {
            $wheres = self::whereClauseForUniquesCheck($fields, $uniques);
            if($wheres) // 唯一索引字段提供了有效值（需要到所有分片中检测记录是否已存在）
            {
                // todo 锁定待插入的数值, 以免多个客户端同时插入相同的数据（比如注册同一用户名）

                $wheres = "WHERE $wheres";
                foreach ($this->shards as $shard) {
                    $info = $shard->findOneWithWhereClause($wheres);
                    if($info !== null) // 记录已存在
                    {
                        if($uniquesCount>1) // 存在多个唯一索引（需要检测这些唯一索引的值与新提供的值是否完全相同）
                        {
                            foreach ($uniques as $unique) {
                                if (count($unique) === 1) // 单列唯一索引
                                {
                                    $fieldName = $unique[0];
                                    if (isset($fields[$fieldName])) {
                                        $value = $fields[$fieldName];
                                        if($value !== $info[$fieldName])
                                            throw new \Exception("该记录已存在, 但存在多个唯一索引列, 其值不完全一致, 无法替换");
                                    }
                                } else  // 多列唯一索引
                                {
                                    $hasValues = false;
                                    foreach ($unique as $fieldName) {
                                        if(isset($fields[$fieldName])) {
                                            $hasValues = true;
                                            break;
                                        }
                                    }
                                    if($hasValues) {
                                        foreach ($unique as $fieldName) {
                                            $value1 = @$fields[$fieldName];
                                            $value2 = @$info[$fieldName];
                                            if($value1 !== $value2)
                                                throw new \Exception("该记录已存在, 但存在多个唯一索引列, 其值不完全一致, 无法替换");
                                        }
                                    }
                                }
                            } // END foreach ($uniques as $unique)
                        } // END if 存在多个唯一索引（需要检测这些唯一索引的值与新提供的值是否完全相同）
                        return $shard->insertOrReplace($fields);
                    } // END if 记录已存在
                } // END foreach ($this->shards as $shard)
            } // END if 唯一索引字段提供了有效值（需要到所有分片中检测记录是否已存在）
        } // END if 存在唯一索引
        // END 检查所有唯一索引

        //如果提供了片键的值, 则直接定位到shard
        if(isset($fields[$this->shardKey]))
        {
            $value = $fields[$this->shardKey];
            $shards = $this->shardsWithFieldValue($this->shardKey, $value); //只会返回一个shard
            return $shards[0]->insertOrReplace($fields);
        }

        // 以下是没有提供片键的值的情况

        if($this->shardKey !== $autoIncrementKey)
            throw new \Exception("表{$this->tableName}启用了分片, 新插入的数据没有指定片键{$this->shardKey}的值, 并且片键字段不是auto increment（不能自动生成）, 所以无法确定新数据要插入的分片");

        // 以下是涉及到autoincrement的insert操作, 片键只会是整型, 不可能是字符串型

        $shardSetp = $this->shardSetp;
        $gapLen = $shardSetp / 10; //step gap用于指定两个分片之间保留的一段auto increment id
        if ($gapLen > 4000) $gapLen = 4000;
        elseif ($gapLen < 1) $gapLen = 1;

        for ($i = $this->shardsFilledCount; $i < $this->shardsCount; $i++) {
            $shard = $this->shards[$i];
            $shardEnd = ($i+1)*$shardSetp;

            // 寻找要插入的分片
            $autoIncrement = $shard->getAutoIncrement(); // 查询当前分片
            if($autoIncrement >= $shardEnd-$gapLen) //当autoIncrement接近分片的上限时, 转到下一个分片,以免在高并发情况下出现insert数据超过分片上限的情况
                continue;

            $insert_id = $shard->insertOrReplace($fields);
            if($insert_id >= $shardEnd) // 插入的记录超过分片上限了
            {
                $shard->deleteByField($autoIncrementKey, $insert_id);
                continue;
            }
            return $insert_id;
        }

        throw new \Exception("表{$this->tableName}的所有分片已满, 无法插入");
    }

    /**
     * @param array $fields 要保存的字段，格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @param $autoIncrementKey string 表的自增字段
     * @param $uniques array[] 表的所有唯一索引,包括主键和联合主键
     * @return string|null 插入的ID; 如果ignored, 返回null
     * @throws \Exception
     */
    function insertOrIgnore(array $fields, $autoIncrementKey = '', $uniques = [])
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->insertOrIgnore($fields);

        // 检查所有唯一索引, 以免并发的情况下重复插入多个分片
        $uniquesCount = count($uniques);
        if(is_array($uniques) && $uniquesCount>0) // 存在唯一索引
        {
            $wheres = self::whereClauseForUniquesCheck($fields, $uniques);
            if($wheres) // 唯一索引字段提供了有效值（需要到所有分片中检测记录是否已存在）
            {
                // todo 锁定待插入的数值, 以免多个客户端同时插入相同的数据（比如注册同一用户名）

                $wheres = "WHERE $wheres";
                foreach ($this->shards as $shard) {
                    $info = $shard->findOneWithWhereClause($wheres);
                    if($info !== null) // 记录已存在
                    {
                        if($uniquesCount>1) // 存在多个唯一索引（需要检测这些唯一索引的值与新提供的值是否完全相同）
                        {
                            foreach ($uniques as $unique) {
                                if (count($unique) === 1) // 单列唯一索引
                                {
                                    $fieldName = $unique[0];
                                    if (isset($fields[$fieldName])) {
                                        $value = $fields[$fieldName];
                                        if($value !== $info[$fieldName])
                                            throw new \Exception("该记录已存在, 但存在多个唯一索引列, 其值不完全一致, 无法忽略（不应该出现此问题, 可能系统设计有问题）");
                                    }
                                } else  // 多列唯一索引
                                {
                                    $hasValues = false;
                                    foreach ($unique as $fieldName) {
                                        if(isset($fields[$fieldName])) {
                                            $hasValues = true;
                                            break;
                                        }
                                    }
                                    if($hasValues) {
                                        foreach ($unique as $fieldName) {
                                            $value1 = @$fields[$fieldName];
                                            $value2 = @$info[$fieldName];
                                            if($value1 !== $value2)
                                                throw new \Exception("该记录已存在, 但存在多个唯一索引列, 其值不完全一致, 无法忽略（不应该出现此问题, 可能系统设计有问题）");
                                        }
                                    }
                                }
                            } // END foreach ($uniques as $unique)
                        } // END if 存在多个唯一索引（需要检测这些唯一索引的值与新提供的值是否完全相同）
                        return null; //忽略的情况
                    } // END if 记录已存在
                } // END foreach ($this->shards as $shard)
            } // END if 唯一索引字段提供了有效值（需要到所有分片中检测记录是否已存在）
        } // END if 存在唯一索引
        // END 检查所有唯一索引

        //如果提供了片键的值, 则直接定位到shard
        if(isset($fields[$this->shardKey]))
        {
            $value = $fields[$this->shardKey];
            $shards = $this->shardsWithFieldValue($this->shardKey, $value); //只会返回一个shard
            return $shards[0]->insertOrIgnore($fields);
        }

        // 以下是没有提供片键的值的情况

        if($this->shardKey !== $autoIncrementKey)
            throw new \Exception("表{$this->tableName}启用了分片, 新插入的数据没有指定片键{$this->shardKey}的值, 并且片键字段不是auto increment（不能自动生成）, 所以无法确定新数据要插入的分片");

        // 以下是涉及到autoincrement的insert操作, 片键只会是整型, 不可能是字符串型

        $shardSetp = $this->shardSetp;
        $gapLen = $shardSetp / 10; //step gap用于指定两个分片之间保留的一段auto increment id
        if ($gapLen > 4000) $gapLen = 4000;
        elseif ($gapLen < 1) $gapLen = 1;

        for ($i = $this->shardsFilledCount; $i < $this->shardsCount; $i++) {
            $shard = $this->shards[$i];
            $shardEnd = ($i+1)*$shardSetp;

            // 寻找要插入的分片
            $autoIncrement = $shard->getAutoIncrement(); // 查询当前分片
            if($autoIncrement >= $shardEnd-$gapLen) //当autoIncrement接近分片的上限时, 转到下一个分片,以免在高并发情况下出现insert数据超过分片上限的情况
                continue;

            $insert_id = $shard->insertOrIgnore($fields);
            if($insert_id === null)
                return null;  //忽略的情况
            else if($insert_id >= $shardEnd) // 插入的记录超过分片上限了
            {
                $shard->deleteByField($autoIncrementKey, $insert_id);
                continue;
            }
            return $insert_id;
        }

        throw new \Exception("表{$this->tableName}的所有分片已满, 无法插入");
    }

    /**
     * @param $primaryKey
     * @param $id
     * @param array $fieldValues
     * @return int 返回更新的行数
     * @throws \Exception
     */
    public function update($primaryKey, $id, array $fieldValues)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->update($primaryKey, $id, $fieldValues);

        //如果提供了片键的值, 则直接定位到shard
        if($primaryKey === $this->shardKey)
        {
            $shards = $this->shardsWithFieldValue($primaryKey, $id); //只会返回一个shard
            return $shards[0]->update($primaryKey, $id, $fieldValues);
        }

        foreach ($this->shards as $shard) {
            $rowCount = $shard->update($primaryKey, $id, $fieldValues);
            if($rowCount>0)
                return $rowCount;
        }
        return 0;
    }

    /**
     * @param $wheres array 例:['fid'=1,'uid'=2]会被展开成'WHERE fid=1 AND uid=2'
     * @param array $fieldValues
     * @return int 返回更新的行数
     * @throws \Exception
     */
    public function updateWhere(array $wheres, array $fieldValues)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->updateWhere($wheres, $fieldValues);

        //如果提供了片键的值, 则直接定位到shard
        if(isset($wheres[$this->shardKey]))
        {
            $value = $wheres[$this->shardKey];
            $shards = $this->shardsWithFieldValue($this->shardKey, $value); //只会返回一个shard
            return $shards[0]->updateWhere($wheres, $fieldValues);
        }

        $rowCount = 0;
        foreach ($this->shards as $shard) {
            $rowCount += $shard->updateWhere($wheres, $fieldValues);
        }
        return $rowCount;
    }

    /**
     * 为指定字段增加/减小值, 并且结果值>=0
     * @param $primaryKey string
     * @param $id mixed 主键值
     * @param array $fieldValues 包含要变化的值的键值对数组
     * @return int 修改的行数
     * @throws \Exception
     */
    public function increaseNonnegatively($primaryKey, $id, array $fieldValues)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->increaseNonnegatively($primaryKey, $id, $fieldValues);

        //如果提供了片键的值, 则直接定位到shard
        if($primaryKey === $this->shardKey)
        {
            $shards = $this->shardsWithFieldValue($primaryKey, $id); //只会返回一个shard
            return $shards[0]->increaseNonnegatively($primaryKey, $id, $fieldValues);
        }

        foreach ($this->shards as $shard) {
            $rowCount = $shard->increaseNonnegatively($primaryKey, $id, $fieldValues);
            if($rowCount>0)
                return $rowCount;
        }
        return 0;
    }

    /**
     * 为指定字段增加/减小值, 并且结果值>=0
     * @param $wheres array 例:['fid'=1,'uid'=2]会被展开成'WHERE fid=1 AND uid=2'
     * @param array $fieldValues 包含要变化的值的键值对数组
     * @return int 修改的行数
     * @throws \Exception
     */
    public function increaseWhereNonnegatively(array $wheres, array $fieldValues)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->increaseWhereNonnegatively($wheres, $fieldValues);

        //如果提供了片键的值, 则直接定位到shard
        if(isset($wheres[$this->shardKey]))
        {
            $value = $wheres[$this->shardKey];
            $shards = $this->shardsWithFieldValue($this->shardKey, $value); //只会返回一个shard
            return $shards[0]->increaseWhereNonnegatively($wheres, $fieldValues);
        }

        $rowCount = 0;
        foreach ($this->shards as $shard) {
            $rowCount += $shard->increaseWhereNonnegatively($wheres, $fieldValues);
        }
        return $rowCount;
    }

    public function replace(Model $dm)
    {
        throw new \Exception("Table::replace()未实现");
    }

    /**
     * @param $field
     * @param $value
     * @return int 返回实际删除的行数
     * @throws \Exception
     */
    public function deleteByField($field, $value)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->deleteByField($field, $value);

        //如果提供了片键的值, 则直接定位到shard
        if($field === $this->shardKey)
        {
            $shards = $this->shardsWithFieldValue($field, $value); //只会返回一个shard
            return $shards[0]->deleteByField($field, $value);
        }

        foreach ($this->shards as $shard) {
            $rowCount = $shard->deleteByField($field, $value);
            if($rowCount>0)
                return $rowCount;
        }
        return 0;
    }

    /**
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @return int 返回实际删除的行数
     * @throws \Exception
     */
    public function deleteByFields($fields)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->deleteByFields($fields);

        //如果提供了片键的值, 则直接定位到shard
        if(isset($fields[$this->shardKey]))
        {
            $value = $fields[$this->shardKey];
            $shards = $this->shardsWithFieldValue($this->shardKey, $value); //只会返回一个shard
            return $shards[0]->deleteByFields($fields);
        }

        $rowCount = 0;
        foreach ($this->shards as $shard) {
            $rowCount += $shard->deleteByFields($fields);
        }
        return $rowCount;
    }

    /**
     * @param IQueryCondition $condition
     * @return int 返回实际删除的行数
     * @throws \Exception
     */
    public function deleteByCondition(IQueryCondition $condition)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->deleteByCondition($condition);

        // todo 优化性能, 在condition中查找是否有片键的值并且是AND条件

        $rowCount = 0;
        foreach ($this->shards as $shard) {
            $rowCount += $shard->deleteByCondition($condition);
        }
        return $rowCount;
    }

    /**
     * 删除表中所有记录
     * @return int 删除的行数
     * @throws \Exception
     */
    public function deleteAll()
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->deleteAll();

        $rowCount = 0;
        foreach ($this->shards as $shard) {
            $rowCount += $shard->deleteAll();
        }
        return $rowCount;
    }

    /**
     * 相当于 TRUNCATE TABLE tablename, 会重置autoIncrement
     * @return int
     * @throws \Exception
     */
    public function truncate()
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->truncate();
        throw new \Exception("表{$this->tableName}启用了分片, 不支持truncate操作");
    }
    
    public function drop()
    {
        if($this->mappingType === self::MappingTypeNormal) {
            $this->shards[0]->drop();
            return;
        }

        foreach ($this->shards as $shard) {
            $shard->drop();
        }
    }

    /**
     * @param $sql
     * @return int 返回受影响的行数
     * @throws \Exception 出错抛出异常
     */
    public function executeSql($sql)
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->executeSql($sql);

        $rowCount = 0;
        foreach ($this->shards as $shard) {
            $rowCount += $shard->executeSql($sql);
        }
        return $rowCount;
    }

    public function beginTransaction()
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->beginTransaction();
        throw new \Exception("表{$this->tableName}启用了分片, 暂不支持事务操作");
    }

    public function commit()
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->commit();
        throw new \Exception("表{$this->tableName}启用了分片, 暂不支持事务操作");
    }

    public function rollBack()
    {
        if($this->mappingType === self::MappingTypeNormal)
            return $this->shards[0]->rollBack();
        throw new \Exception("表{$this->tableName}启用了分片, 暂不支持事务操作");
    }

}