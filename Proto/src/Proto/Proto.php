<?php
/*
 * Project: study
 * File: Proto.php
 * CreateTime: 16/2/6 19:50
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file Proto.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Proto;

use \WebGeeker\Rest\Table;

/**
 * @class Proto
 * @brief brief description
 *
 * elaborate description
 */
class Proto
{
    const DBTypeMysql = 0;
    const DBTypeMongoDB = 1;

    protected $prototypeName; //原型名（相当于表名）
    protected $fields;

    protected $engine; // 数据库引擎（MyISAM|innoDB）
    protected $autoIncrement;
    protected $charset; //默认字符集（ascii|utf8）

    protected $dbType; //数据库类型（mysql|mongoDB）

    public function addField(ProtoField $field)
    {
        $this->fields[] = $field;
//        $this->array[] = new ProtoField($fieldName, $type, $comment, $isPk);
    }

    /**
     * @return ProtoField[]
     */
    protected function getFields()
    {
        return $this->fields;
    }
    public function getCreateTableString()
    {
        $string = "CREATE TABLE IF NOT EXISTS `{$this->prototypeName}` (\n";

        foreach ($this->getFields() as $field) {
            $string .= "  $field,\n";
        }
        $string = substr($string, 0, strlen($string)-2); //删除最后一个(,\n)
        $string .= "\n";
        $string .= ")";
        if(isset($this->engine))
            $string .= " ENGINE={$this->engine}";
        $autoIncrement = intval($this->autoIncrement);
        if($autoIncrement>0)
            $string .= " AUTO_INCREMENT=$autoIncrement";
        if(isset($this->charset)) {
            if ($this->charset == ProtoField::CharsetAscii)
                $string .= ' DEFAULT CHARSET ascii';
            else if ($this->charset == ProtoField::CharsetUtf8)
                $string .= ' DEFAULT CHARSET utf8';
        }
        $string .= ";\n";
        return $string;
    }

    public function getDropTableString()
    {
        return "DROP TABLE IF EXISTS `{$this->prototypeName}`;";
    }

    public function create()
    {
        Table::executeSql($this->getCreateTableString());
    }

    public function drop()
    {
        Table::executeSql($this->getDropTableString());
    }
}