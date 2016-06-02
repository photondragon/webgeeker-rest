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
    protected $database; //数据库连接|数据库实例。Database类型
    protected $tableName; //表名。mysql表名字段名都是忽略大小写的

    public function __construct(Database $database, $tableName)
    {
        $this->database = $database;
        $this->tableName = $tableName;
    }

    protected $bufferedStmts = array();

    /**
     * @param $stmtString
     * @return \PDOStatement
     */
    protected function prepareStmt($stmtString)
    {
        $stmt = @$this->bufferedStmts[$stmtString];
        if ($stmt===null) {
            $stmt = $this->database->pdo->prepare($stmtString);
            $this->bufferedStmts[$stmtString] = $stmt;
        }
        return $stmt;
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
        if (strlen($fieldName)==0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 参数fieldName无效");

        $stmtString = "SELECT $fieldName FROM `{$this->tableName}` WHERE $fieldName=? LIMIT 1";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute([$value]);
        $info = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($info===false)
            return false;
        return true;
    }

    /**
     * @param $fieldName
     * @param $value
     * @return null|array
     * @throws \Exception
     */
    public function findByField($fieldName, $value)
    {
        if (strlen($fieldName)==0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 参数fieldName无效");

        $stmtString = "SELECT * FROM `{$this->tableName}` WHERE $fieldName=? LIMIT 1";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute([$value]);
        $info = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($info===false)
            return null;
        return $info;
    }

    /**
     * 相当于 SELECT * WHERE field IN(?,?,?) 语句
     * @param $fieldName string
     * @param $valueList array
     * @return array[]
     * @throws \Exception
     */
    public function findAllInFieldValueList($fieldName, $valueList)
    {
        $count = count($valueList);
        if (strlen($fieldName)==0 || $count===0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $array = [];
        for ($i = 0; $i < $count; $i++) {
            $array[] = '?';
        }
        $str = implode(',', $array);
        $stmtString = "SELECT * FROM `{$this->tableName}` WHERE $fieldName IN($str)";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($valueList);
        $infos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $infos;
    }

    /**
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @return null|array
     * @throws \Exception
     */
    public function findByFields($fields)
    {
        if (is_array($fields)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $isFirst = true;
        $params = [];
        $condition = '';
        foreach ($fields as $field => $value) {
            if ($isFirst) {
                $condition = "`$field`=?";
                $isFirst = false;
            }
            else
                $condition .= " AND `$field`=?";
            $params[] = $value;
        }

        $stmtString = "SELECT * FROM `{$this->tableName}` WHERE $condition LIMIT 1";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($params);
        $info = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($info==false)
            return null;
        return $info;
    }
    /**
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @param $increaseField string 要增加的字段名
     * @param $deltaValue int 要增加的数值, 可正可负
     * @return null|array
     * @throws \Exception
     */
    public function findAndIncreaseByFields($fields, $increaseField, $deltaValue)
    {
        if (is_array($fields)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $isFirst = true;
        $params = [];
        $condition = '';
        foreach ($fields as $field => $value) {
            if ($isFirst) {
                $condition = "`$field`=:$field";
                $isFirst = false;
            }
            else {
                $condition .= " AND `$field`=:$field";
            }
            $params[$field] = $value;
        }
        // 自增
        $stmtString = "UPDATE `{$this->tableName}` SET $increaseField=$increaseField+($deltaValue) WHERE $condition LIMIT 1;\n";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($params);
        
        $stmtString = "SELECT * FROM `{$this->tableName}` WHERE $condition LIMIT 1;";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($params);
        $info = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($info==false)
            return null;
        return $info;
    }

    /**
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @return array[]
     * @throws \Exception
     */
    public function findAllByFields($fields)
    {
        if (is_array($fields)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $isFirst = true;
        $params = [];
        $condition = '';
        foreach ($fields as $field => $value) {
            if ($isFirst) {
                $condition = "`$field`=?";
                $isFirst = false;
            }
            else
                $condition .= " AND `$field`=?";
            $params[] = $value;
        }

        $stmtString = "SELECT * FROM `{$this->tableName}` WHERE $condition";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($params);
        $infos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $infos;
    }

    /**
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @return int
     * @throws \Exception
     */
    public function countByFields($fields)
    {
        if (is_array($fields)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $isFirst = true;
        $params = [];
        $condition = '';
        foreach ($fields as $field => $value) {
            if ($isFirst) {
                $condition = "`$field`=?";
                $isFirst = false;
            }
            else
                $condition .= " AND `$field`=?";
            $params[] = $value;
        }

        $stmtString = "SELECT COUNT(*) AS c FROM `{$this->tableName}` WHERE $condition";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($params);
        $infos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return (int)@$infos[0]['c'];
    }

    /**
     * @param IQuery $query
     * @return array[]
     */
    public function findAllWithQuery(IQuery $query)
    {
        $sql = $query->getQueryString($this->tableName);
        $stmt = $this->database->pdo->query($sql);
        $infos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $infos;
    }

    static private function correctValue($value)
    {
        $type = gettype($value);
        if($type==='array' || $type==='object')
            return json_encode($value, JSON_PRESERVE_ZERO_FRACTION);
//        else if($type==='double') {
//            $decimals = 16; //小数点后的位数
//            if($value<1 && $value>-1) //如果整数部分是0, 则要提高小数点后的位数
//            {
//                $value2 = $value*100000;
//                $decimals += 5;
//                while($value2>-1 && $value2<1)
//                {
//                    $value2 *= 100000;
//                    $decimals +=5;
//                }
//            }
//            $v = number_format($value, $decimals, '.', '');
//            return $v;
//        }
        else if($type==='boolean') // mysql中bool用tinyint存储, sqlite中bool用boolean存储
            return $value ? 1 : 0;
        return $value;
    }

    /**
     * @param array $fields 要保存的字段，格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @return string 插入的ID
     * @throws \Exception
     */
    function insert(array $fields)
    {
        $fieldNames = [];
        $fieldValues = [];
        array_walk($fields, function(&$value, $key) use(&$fieldNames, &$fieldValues){
            $fieldNames[] = $key;
            $fieldValues[$key] = Table::correctValue($value);
        });
        $fieldsString = implode('`,`', $fieldNames);
        $valuesString = ':' . implode(',:', $fieldNames);
        $stmtString = "INSERT INTO `{$this->tableName}`(`$fieldsString`) VALUES($valuesString)";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($fieldValues);
        return $this->database->pdo->lastInsertId();
    }

    /**
     * @param array $fields 要保存的字段，格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @return string 插入的ID
     * @throws \Exception
     */
    function insertOrReplace(array $fields)
    {
        $fieldNames = [];
        $fieldValues = [];
        $updates = [];
        array_walk($fields, function(&$value, $key) use(&$fieldNames, &$fieldValues, &$updates){
            $fieldNames[] = $key;
            $updates[] = "$key=:{$key}2";
            $fieldValues[$key] = Table::correctValue($value);
            $fieldValues[$key.'2'] = $fieldValues[$key];
        });
        $fieldsString = implode('`,`', $fieldNames);
        $valuesString = ':' . implode(',:', $fieldNames);
        $updateFieldsString = implode(',', $updates);
        $stmtString = "INSERT INTO `{$this->tableName}`(`$fieldsString`) VALUES($valuesString) ON DUPLICATE KEY UPDATE $updateFieldsString";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($fieldValues);
        return $this->database->pdo->lastInsertId();
    }

    /**
     * @param array $fields 要保存的字段，格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @return string|null 插入的ID; 如果ignored, 返回null
     * @throws \Exception
     */
    function insertOrIgnore(array $fields)
    {
        $fieldNames = [];
        $fieldValues = [];
        array_walk($fields, function(&$value, $key) use(&$fieldNames, &$fieldValues){
            $fieldNames[] = $key;
            $fieldValues[$key] = Table::correctValue($value);
        });
        $fieldsString = implode('`,`', $fieldNames);
        $valuesString = ':' . implode(',:', $fieldNames);
        $stmtString = "INSERT IGNORE INTO `{$this->tableName}`(`$fieldsString`) VALUES($valuesString)";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($fieldValues);
        if($stmt->rowCount()===0) //ignored
            return null;
        return $this->database->pdo->lastInsertId();
    }

    public function update($primaryKey, $id, array $fieldValues)
    {
        $fieldStrings = [];
        $values = [];
        array_walk($fieldValues, function(&$value, $key) use(&$fieldStrings, &$values){
            $fieldStrings[] = "$key=?";
            $values[] = Table::correctValue($value);
        });
        $values[] = $id;
        $fieldsString = implode(',', $fieldStrings);
        $stmtString = "UPDATE `{$this->tableName}` SET $fieldsString WHERE `$primaryKey`=?";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($values);
        return $stmt->rowCount();
    }

    /**
     * @param $wheres array 例:['fid'=1,'uid'=2]会被展开成'WHERE fid=1 AND uid=2'
     * @param array $fieldValues
     * @return int
     */
    public function updateWhere(array $wheres, array $fieldValues)
    {
        $fieldStrings = [];
        $values = [];
        array_walk($fieldValues, function(&$value, $key) use(&$fieldStrings, &$values){
            $fieldStrings[] = "`$key`=?";
            $values[] = Table::correctValue($value);
        });
        $whereKeys = [];
        foreach ($wheres as $key=>$value) {
            $whereKeys[] = "`$key`=?";
            $values[] = $value;
        }
        $whereString = implode(' AND ', $whereKeys);
        $fieldsString = implode(',', $fieldStrings);
        $stmtString = "UPDATE `{$this->tableName}` SET $fieldsString WHERE $whereString";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($values);
        return $stmt->rowCount();
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
        $fieldStrings = [];
        $values = [];
        $conds = [];

        foreach ($fieldValues as $key => $value) {
            if(!(is_int($value) || is_float($value) || is_double($value)))
                throw new \Exception('Inc的值必须是数值类型');
            $fieldStrings[] = "`$key`=`$key`+?";
            $values[] = Table::correctValue($value);
            if($value<0) //减去一个值
                $conds[$key] = -$value;
        }
        $fieldsString = implode(',', $fieldStrings);
        $values[] = $id;

        $condStrings = [];
        foreach ($conds as $key => $value) {
            $condStrings[] = "`$key`>?";
            $values[] = Table::correctValue($value);
        }
        $condsString = implode(' AND ', $condStrings);

        $stmtString = "UPDATE `{$this->tableName}` SET $fieldsString WHERE `$primaryKey`=? AND $condsString";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($values);
        return $stmt->rowCount();
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
        $fieldStrings = [];
        $values = [];
        $conds = [];

        foreach ($fieldValues as $key => $value) {
            if(!(is_int($value) || is_float($value) || is_double($value)))
                throw new \Exception('Inc的值必须是数值类型');
            $fieldStrings[] = "`$key`=`$key`+?";
            $values[] = Table::correctValue($value);
            if($value<0) //减去一个值
                $conds[$key] = -$value;
        }
        $fieldsString = implode(',', $fieldStrings);

        $whereKeys = [];
        foreach ($wheres as $key=>$value) {
            $whereKeys[] = "`$key`=?";
            $values[] = Table::correctValue($value);
        }
        $whereString = implode(' AND ', $whereKeys);

        $condStrings = [];
        foreach ($conds as $key => $value) {
            $condStrings[] = "`$key`>?";
            $values[] = Table::correctValue($value);
        }
        $condsString = implode(' AND ', $condStrings);

        $stmtString = "UPDATE `{$this->tableName}` SET $fieldsString WHERE $whereString AND $condsString";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($values);
        return $stmt->rowCount();
    }

    public function replace(Model $dm)
    {

    }

    /**
     * @param $field
     * @param $value
     * @return int 返回实际删除的行数
     * @throws \Exception
     */
    public function deleteByField($field, $value)
    {
        if (strlen($field)==0 || isset($value)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $stmtString = "DELETE FROM `{$this->tableName}` WHERE $field=?";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute([$value]);
        return $stmt->rowCount();
    }

    /**
     * @param $fields array 格式['field1'=>$value1, 'field2'=>$value2, ...]
     * @return int 返回实际删除的行数
     * @throws \Exception
     */
    public function deleteByFields($fields)
    {
        if (is_array($fields)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $isFirst = true;
        $params = [];
        $condition = '';
        foreach ($fields as $field => $value) {
            if ($isFirst) {
                $condition = "$field=?";
                $isFirst = false;
            }
            else
                $condition .= " AND $field=?";
            $params[] = $value;
        }

        $stmtString = "DELETE FROM `{$this->tableName}` WHERE $condition";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * @param IQueryCondition $condition
     * @return int 返回实际删除的行数
     * @throws \Exception
     */
    public function deleteByCondition(IQueryCondition $condition)
    {
        $condstr = (string)$condition;
        if(strlen($condstr)===0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数\$condition");
        $stmtString = "DELETE FROM `{$this->tableName}` WHERE $condstr";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * 相当于 TRUNCATE TABLE tablename
     * @param $primaryKey string 主键名
     * @return int 删除的行数
     */
    public function deleteAll($primaryKey)
    {
        $stmtString = "DELETE FROM `{$this->tableName}`";// WHERE `$primaryKey` != 0";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function truncate()
    {
        $stmtString = "TRUNCATE TABLE `{$this->tableName}`";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * @param $sql
     * @return int 返回受影响的行数
     * @throws \Exception 出错抛出异常
     */
    public function executeSql($sql)
    {
        return $this->database->executeSql($sql);
    }

    public function beginTransaction()
    {
        $this->database->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->database->pdo->commit();
    }

    public function rollBack()
    {
        $this->database->pdo->rollBack();
    }

}