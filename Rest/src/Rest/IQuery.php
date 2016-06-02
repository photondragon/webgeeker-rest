<?php
/*
 * Project: simpleim-php
 * File: IQuery.php
 * CreateTime: 16/4/25 21:54
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file IQuery.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Rest;


interface IQueryCondition
{
    public function __toString();
}

interface IQueryConditionCompare extends IQueryCondition
{
    public function __construct($field, $op, $value); //例: new MysqlConditionCompare('id', '>', 0);
}

interface IQueryConditionIn extends IQueryCondition
{
    public function __construct($field, array $values); //例: new MysqlConditionIn('id', [1,2,3]);
}

interface IQueryConditionAnd extends IQueryCondition
{
    public function addCondition(IQueryCondition $condition);
}

interface IQueryConditionOr extends IQueryCondition
{
    public function __construct(IQueryCondition $cond1, IQueryCondition $cond2);
}

/**
 * 括号条件（在条件表达式两边加上括号）
 * Interface IQueryConditionParenthesis
 * @package WebGeeker\Rest
 */
interface IQueryConditionParenthesis extends IQueryCondition
{
    public function __construct(IQueryCondition $condition);
}

/**
 * Interface IQuery
 * @package WebGeeker\Rest
 * @brief 数据库查询请求的接口
 *
 * elaborate description
 */
interface IQuery
{
    /**
     * 设置查询要返回哪些字段
     * @param $fields string|string[] 设置SELECT要返回的字段。默认是*
     * @throws \Exception
     */
    public function select($fields);

    /**
     * 设置查询条件
     * @param IQueryCondition $condition
     * @return mixed
     */
    public function where(IQueryCondition $condition);

    /**
     * 设置排序字段. 可以分多次设置多个排序字段
     * 相当于Mysql中的ORDER BY
     * @param $fieldName
     * @param string $order 三种取值: 'asc'-升序, 'desc'-降序, ''-无
     */
    public function orderBy($fieldName, $order = '');

    /**
     * 设置截取结果集中的一段
     * 相当于Mysql中的LIMIT
     * $offset>0 && $count==0 这种情况是无效的. 只有$count>0 才会分段
     * @param $count int 个数. 0表示全部
     * @param $offset int 起始位置
     */
    public function segment($count, $offset = 0);

    /**
     * 获取最终的查询字符串
     * @param $tableName string 表名
     * @return string
     */
    public function getQueryString($tableName);

    /**
     * @param $field
     * @param $op
     * @param $value
     * @return IQueryConditionCompare
     */
    public function createConditionCompare($field, $op, $value);

    /**
     * @param $field
     * @param array $values
     * @return IQueryConditionIn
     */
    public function createConditionIn($field, array $values);

    /**
     * @return IQueryConditionAnd
     */
    public function createConditionAnd();

    /**
     * @param IQueryCondition $cond1
     * @param IQueryCondition $cond2
     * @return IQueryConditionOr
     */
    public function createConditionOr(IQueryCondition $cond1, IQueryCondition $cond2);

    /**
     * @param IQueryCondition $condition
     * @return IQueryConditionParenthesis
     */
    public function createConditionParenthesis(IQueryCondition $condition);

}