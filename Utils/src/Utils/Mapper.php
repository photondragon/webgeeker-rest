<?php

namespace WebGeeker\Utils;

use \WebGeeker\Rest\Model;

abstract class Mapper
{
    protected static $pdo;
    function __construct()
    {
        if ( ! isset(self::$pdo) ) {
//            $dsn = Registry::get('pdo');
//            if ( is_null( $dsn ) ) {
//                throw new \woo\base\AppException( "No DSN" );
//            }
            $dbuser = 'study';
            $dbpass = 'study';
            $dsn = "mysql:host={$dbuser};dbname=${dbpass}";
            self::$pdo = new \PDO( $dsn );
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
    }

//    private function getFromMap( $id )
//    {
//        return ModelRegistry::exists( $this->targetClass(), $id );
//    }
//
//    private function addToMap(Model $obj)
//    {
//        ModelRegistry::register($obj);
//    }
//
    function find( $id ) {
//        $old = $this->getFromMap( $id );
//        if ( $old )
//        {
//            return $old;
//        }

        $this->selectstmt()->execute( array( $id ) );
        $array = $this->selectstmt()->fetch();
        $this->selectstmt()->closeCursor( );
        if ( ! is_array( $array ) ) { return null; }
        if ( ! isset( $array['id'] ) ) { return null; }
        $object = $this->createObject( $array );
        $object->markClean();
        return $object; 
    }

    function findAll()
    {
        $this->selectAllStmt()->execute( array() );
        return $this->getCollection( $this->selectAllStmt()->fetchAll( \PDO::FETCH_ASSOC ) );
    }
 
    function createObject($array)
    {
//        $old = $this->getFromMap($array['id']);
//        if ( $old )
//        {
//            return $old;
//        }
        $obj = $this->doCreateObject($array);
//        $this->addToMap($obj);
        //$obj->markClean();
        return $obj;
    }

    function insert(Model $obj)
    {
        $this->doInsert($obj);
//        $this->addToMap($obj);
//        $obj->markClean();
    }

    protected function doCreateObject(array $array)
    {
        return new Model();
    }

//  abstract function update( \woo\domain\DomainObject $object );
    abstract protected function getCollection(array $raw);
    abstract protected function doInsert(Model $object);
    abstract protected function targetClass();
    abstract protected function selectStmt();
    abstract protected function selectAllStmt();
}

