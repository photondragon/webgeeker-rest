<?php
/*
 * Project: study
 * File: MysqlQuery.php
 * CreateTime: 16/1/27 00:02
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file MysqlQuery.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Rest;

/**
 * @class MysqlQuery
 * @brief brief description
 *
 * elaborate description
 */
class MysqlQuery implements IQuery
{
    protected $select = '*';
    protected $where;
    protected $orderBys = [];
    protected $sortFields = [];
    protected $offset = 0;
    protected $count = 0; //0表示全部

    /**
     * @param $fields string|string[] 设置SELECT要返回的字段。默认是*
     * @throws \Exception
     */
    public function select($fields)
    {
        if (is_string($fields)) {
            if(strlen($fields)===0)
                $this->select = '*';
            else
                $this->select = MysqlQuery::sqlEscapeColumnName($fields);
            return;
        }
        elseif (is_array($fields))
        {
            if (count($fields)===0)
            {
                $this->select = '*';
                return;
            }
            else {
                $invalidField = false;
                array_walk($fields, function (&$fieldName) use (&$invalidField) {
                    if (is_string($fieldName) === false)
                        $invalidField = true;
                    else
                        $fieldName = MysqlQuery::sqlEscapeColumnName($fieldName);
                });
                if ($invalidField === false) {
                    $this->select = implode(',', $fields);
                    return;
                }
            }
        }

        throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数。\$fields必是字符串的数组");
    }

    public function where(IQueryCondition $condition)
    {
        $this->where = $condition;
    }

    /**
     * 设置排序字段. 可以分多次设置多个排序字段
     * 相当于Mysql中的ORDER BY
     * @param $fieldName
     * @param int $order 三种取值: OrderAsc-升序, OrderDesc-降序, OrderNone-默认排序
     */
    public function orderBy($fieldName, $order = self::OrderNone)
    {
        if(strlen($fieldName)){
            if(strtolower($order)==='asc')
                $order = self::OrderAsc;
            elseif(strtolower($order)==='desc')
                $order = self::OrderDesc;
            elseif($order==='')
                $order = self::OrderNone;
            else
                return;
            if($order === self::OrderAsc)
                $this->orderBys[] = "`$fieldName` ASC";
            elseif($order === self::OrderDesc)
                $this->orderBys[] = "`$fieldName` DESC";
            elseif($order === self::OrderNone)
                $this->orderBys[] = "`$fieldName`";
            else
                return;
            $this->sortFields[] = [$fieldName, $order];
        }
    }

    public function getSortFields(){
        return $this->sortFields;
    }

    /**
     * 设置截取结果集中的一段
     * 相当于Mysql中的LIMIT
     * $offset>0 && $count==0 这种情况是无效的. 只有$count>0 才会分段
     * @param $count int 个数. 0表示全部
     * @param $offset int 起始位置
     */
    public function segment($count, $offset = 0)
    {
        if($offset<0)
            $offset = 0;
        if($count<0)
            $count = 0;
        if($count>100000)
            $count = 100000;
        $this->offset = $offset;
        $this->count = $count;
    }

    public function getCount(){
        return $this->count;
    }
    public function getOffset(){
        return $this->offset;
    }

    public function getQueryString($tableName)
    {
        $sql = "SELECT $this->select FROM `$tableName`";
        if (isset($this->where)) {
            $where = (string)$this->where;
            if (strlen($where)>0)
                $sql .= " WHERE $where";
        }
        if(count($this->orderBys)){
            $sql .= ' ORDER BY ' . implode(',', $this->orderBys);
        }
        if($this->offset && $this->count)
            $sql .= " LIMIT {$this->offset},$this->count";
        else if($this->count)
            $sql .= " LIMIT $this->count";
        return $sql;
    }

    /**
     * @return IQueryConditionAnd
     */
    public function createConditionAnd()
    {
        $andCond = new MysqlConditionAnd();

        if(func_num_args()>0) // 参数个数
        {
            $args = func_get_args(); //获取所有参数的数组
            foreach ($args as $arg) {
                if($arg instanceof IQueryCondition)
                    $andCond->addCondition($arg);
            }
        }

        return $andCond;
    }

    /**
     * @param IQueryCondition $cond1
     * @param IQueryCondition $cond2
     * @return IQueryConditionOr
     */
    public function createConditionOr(IQueryCondition $cond1, IQueryCondition $cond2)
    {
        return new MysqlConditionOr($cond1, $cond2);
    }

    /**
     * @param $field
     * @param $op
     * @param $value
     * @return IQueryConditionCompare
     */
    public function createConditionCompare($field, $op, $value)
    {
        return new MysqlConditionCompare($field, $op, $value);
    }

    public function createConditionIn($field, array $values)
    {
        return new MysqlConditionIn($field, $values);
    }

    /**
     * @param IQueryCondition $condition
     * @return IQueryConditionParenthesis
     */
    public function createConditionParenthesis(IQueryCondition $condition)
    {
        return new MysqlConditionParenthesis($condition);
    }

    public static function sqlEscapeColumnName($string)
    {
        if(is_string($string))
            return addcslashes($string, "\"'\\\r\n\t\0\x08");
        throw new \Exception(__FUNCTION__ . "(): 无效参数");
    }

    /**
     * 将各种数值转换成字符串, 用于拼接sql的Where子句
     * @param $value
     * @return string
     * @throws \Exception
     */
    public static function sqlEscapeValueForWhereClause($value)
    {
        $type = gettype($value);
        if($type==='string')
            $value = '\'' . addcslashes($value, "\"'\\\r\n\t%_\0\x08") . '\'';
        else if($type==='boolean')
            $value = $value ? 'true' : 'false';
        else if($type==='NULL')
            $value = 'NULL';
        else if($type==='object')
            throw new \Exception('WHERE子句中只能包含标量值');
        else if($type==='array')
            throw new \Exception('WHERE子句中只能包含标量值');
        else  // 整型浮点型
            $value = (string)$value;
        return $value;
    }
}

class MysqlConditionCompare implements IQueryConditionCompare
{
    private $field;
    private $operator;
    private $value;

    public function __construct($field, $op, $value)
    {
        if (strlen($field)===0 || strlen($op)===0 || isset($value)===false)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");
        $this->field = $field;
        $this->operator = $op;
        $this->value = $value;
    }

    public function __toString()
    {
        $field = MysqlQuery::sqlEscapeColumnName($this->field);
        $op = $this->operator;
        $valueString = MysqlQuery::sqlEscapeValueForWhereClause($this->value);
        return "`$field` $op $valueString";
    }
}

class MysqlConditionIn implements IQueryConditionIn
{
    private $field;
    private $values;

    public function __construct($field, array $values)
    {
        if (strlen($field)===0 || count($values)===0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 无效参数");
        $this->field = $field;
        $filtered = [];
        foreach ($values as $value) {
            $filtered[] = MysqlQuery::sqlEscapeValueForWhereClause($value);
        }
        $this->values = $filtered;
    }

    public function __toString()
    {
        $field = MysqlQuery::sqlEscapeColumnName($this->field);
        $valuesString = implode(',', $this->values);
        return "`$field` IN($valuesString)";
    }
}

class MysqlConditionOr implements IQueryConditionOr
{
    private $condition1;
    private $condition2;

    public function __construct(IQueryCondition $cond1, IQueryCondition $cond2)
    {
        $this->condition1 = $cond1;
        $this->condition2 = $cond2;
    }

    public function __toString()
    {
        return $this->condition1 . ' OR ' . $this->condition2;
    }
}

class MysqlConditionAnd implements IQueryConditionAnd
{
    private $conditions;

    public function addCondition(IQueryCondition $condition)
    {
        $this->conditions[] = $condition;
    }

    public function __toString()
    {
        if (count($this->conditions)===0)
            return '';
        return (string)implode(' AND ', $this->conditions);
    }
}

/**
 * @class MysqlConditionParenthesis
 * @brief brief 给已有条件加上括号，形成新条件
 *
 * elaborate 只能包住一个条件（但这个条件可以是AND或OR条件）
 */
class MysqlConditionParenthesis implements IQueryConditionParenthesis
{
    private $condition;
    public function __construct(IQueryCondition $condition)

    {
        $this->condition = $condition;
    }

    public function __toString()
    {
        return '(' . $this->condition . ')';
    }
}

