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
     * @param $field
     * @param $value
     * @return null|array
     * @throws \Exception
     */
    public function findByField($field, $value)
    {
        if (strlen($field)==0 || isset($value)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");

        $stmtString = "SELECT * FROM {$this->tableName} WHERE $field=? LIMIT 1";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute([$value]);
        $info = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($info==false)
            return null;
        return $info;
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
                $condition = "$field=?";
                $isFirst = false;
            }
            else
                $condition .= " AND $field=?";
            $params[] = $value;
        }

        $stmtString = "SELECT * FROM {$this->tableName} WHERE $condition LIMIT 1";
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
                $condition = "$field=?";
                $isFirst = false;
            }
            else
                $condition .= " AND $field=?";
            $params[] = $value;
        }

        $stmtString = "SELECT * FROM {$this->tableName} WHERE $condition";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($params);
        $infos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $infos;
    }

    /**
     * @param MysqlQuery $query
     * @return array[]
     */
    public function findAllWithQuery(MysqlQuery $query)
    {
        $sql = $query->getSqlString($this->tableName);
        $stmt = $this->database->pdo->query($sql);
        $infos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $infos;
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
            $type = gettype($value);
            if($type==='array' || $type==='object')
                $fieldValues[$key] = json_encode($value);
            else
                $fieldValues[$key] = $value;
        });
        $fieldsString = implode(',', $fieldNames);
        $valuesString = ':' . implode(',:', $fieldNames);
        $stmtString = "INSERT INTO {$this->tableName}($fieldsString) VALUES($valuesString)";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute($fieldValues);
        return $this->database->pdo->lastInsertId();
    }

    public function update($id, array $fields)
    {
        $fieldStrings = [];
        $values = [];
        array_walk($fields, function(&$value, $key) use(&$fieldStrings, &$values){
            $fieldStrings[] = "$key=?";
            $type = gettype($value);
            if($type==='array' || $type==='object')
                $values[] = json_encode($value);
            else
                $values[] = $value;
        });
        $values[] = $id;
        $fieldsString = implode(',', $fieldStrings);
        $stmtString = "UPDATE {$this->tableName} SET $fieldsString WHERE id=?";
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

        $stmtString = "DELETE FROM {$this->tableName} WHERE $field=?";
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

        $stmtString = "DELETE FROM {$this->tableName} WHERE $condition";
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
        $stmtString = "DELETE FROM {$this->tableName} WHERE $condstr";
        $stmt = $this->prepareStmt($stmtString);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function executeSql($sql)
    {
        $ret = $this->database->pdo->exec($sql);
        if($ret===false)
            $this->database->pdo->errorInfo();
        elseif ($ret===0)
            echo 'return 0';
    }
}