<?php
/*
 * Project: study
 * File: ProtoField.php
 * CreateTime: 16/2/6 20:00
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file ProtoField.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Proto;

/**
 * @class ProtoField
 * @brief brief description
 *
 * elaborate description
 */
class ProtoField
{
    const TypeString = null; //变长字符串，相当于Mysql的VARCHAR(n)。最大65532
    const TypeInt32 = 1;
    const TypeInt64 = 2;
    const TypeFloat = 3;
    const TypeDouble = 4;
    const TypeChars = 5; //定长字符串，相当Mysql的CHAR(n)。最大255
    const TypeLongText = 6; //长文本，不支持索引，相当Mysql的longtext，最大2^32-1

    const CharsetDefault = null;
    const CharsetUtf8 = 1;
    const CharsetAscii = 2;

    const IndexOrderNone = null;
    const IndexOrderAsc = 1;
    const IndexOrderDesc = 2;

    public $name;
    public $type;
    public $unsigned;
    public $stringLength; //字符串长度。只对TypeChars|TypeString有效
    public $charset; //只在type是字符串时有效
    public $notNull; //是否不可为空
    public $default; //默认值
    public $autoIncrement;
    public $unique; //是否唯一
    public $primary; //是否主键
    public $comment;
    public $indexOrder; //索引顺序(IndexOrderNone|IndexOrderAsc|IndexOrderDesc)

    public function __construct($name, $type, $stringLength = 0, $comment = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->stringLength = $stringLength;
        $this->comment = $comment;
    }

    public function __toString()
    {
        $filedName = lcfirst($this->name);
        if(strlen($filedName)===0)
            throw new \Exception(get_class($this) . '::' . __FUNCTION__ . "(): 必须设置field name");

        $typeString = $this->getTypeString();

        $str = "`$filedName` $typeString";

        if($this->notNull)
            $str .= ' NOT NULL';
        if(isset($this->default)) {
            $default = self::mysqlEscapeString($this->default);
            $str .= " DEFAULT '$default'";
        }
        if($this->autoIncrement)
            $str .= ' AUTO_INCREMENT';
        if($this->unique)
            $str .= ' UNIQUE';
        if($this->primary)
            $str .= ' PRIMARY KEY';
        if(strlen($this->comment)>0) {
            $comment = self::mysqlEscapeString($this->comment);
            $str .= " COMMENT '$comment'";
        }

        if($this->indexOrder==self::IndexOrderAsc)
            $str .= ",\n  INDEX `$filedName` (`$filedName` ASC)";
        elseif($this->indexOrder==self::IndexOrderDesc)
            $str .= ",\n  INDEX `$filedName` (`$filedName` DESC)";
        return $str;
    }

    private function getTypeString()
    {
        switch ($this->type) {
            case self::TypeInt32:
            {
                $typeString = 'int';
                if($this->unsigned)
                    $typeString .= ' unsigned';
                break;
            }
            case self::TypeInt64:
            {
                $typeString = 'bigint';
                if($this->unsigned)
                    $typeString .= ' unsigned';
                break;
            }
            case self::TypeFloat:
            {
                $typeString= 'float';
                if($this->unsigned)
                    $typeString .= ' unsigned';
                break;
            }
            case self::TypeDouble:
            {
                $typeString= 'double';
                if($this->unsigned)
                    $typeString .= ' unsigned';
                break;
            }
            case self::TypeString:
            case self::TypeChars:
            case self::TypeLongText:
            {
                $len = intval($this->stringLength);
                if($len<1)
                    $len = 255;

                if($this->type==self::TypeChars)
                    $typeString= "char($len)";
                elseif($this->type==self::TypeLongText)
                    $typeString= "longtext";
                else //if($this->type==self::TypeString)
                    $typeString= "varchar($len)";

                if(isset($this->charset)) {
                    if ($this->charset == self::CharsetAscii)
                        $typeString .= ' CHARSET ascii';
                    else if ($this->charset == self::CharsetUtf8)
                        $typeString .= ' CHARSET utf8';
                }
                break;
            }
            default:
            {
                throw new \Exception('没有设置类型');
                break;
            }
        }
        return $typeString;
    }

    protected static function mysqlEscapeString($string)
    {
        if(is_string($string))
            return addcslashes($string, "\"'\\\r\n\t%_\0\x08");
        throw new \Exception(__FUNCTION__ . "(): 无效参数");
    }
}