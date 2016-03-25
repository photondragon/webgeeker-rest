<?php
/*
 * Project: study
 * File: Registry.php
 * CreateTime: 16/1/22 18:04
 * Author: photondragon
 * Email: photondragon@163.com
 */
/**
 * @file Registry.php
 * @brief brief description
 *
 * elaborate description
 */

namespace WebGeeker\Utils;

class Registry
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new JoomlaRegistry();
        }
        return self::$instance;
    }

    public static function get( $key )
    {
        return self::getInstance()->get($key);
    }

    public static function set( $key, $val )
    {
        self::getInstance()->set($key, $val);
    }
}

interface IRegistry
{
    public function get( $key );
    public function set( $key, $val );
}

class JoomlaRegistry implements IRegistry
{
    private $registry;

    public function __construct()
    {
        $this->registry = new \Joomla\Registry\Registry();
    }

    public function get( $key )
    {
        return $this->registry->get($key);
    }

    public function set( $key, $val )
    {
        $this->registry->set($key, $val);
    }
}

class RequestRegistry implements IRegistry
{
    private $values = array();

    private function __construct() {}

    public function get( $key ) {
        if ( isset( $this->values[$key] ) ) {
            return $this->values[$key];
        }
        return null;
    }

    public function set( $key, $val ) {
        $this->values[$key] = $val;
    }
}

class SessionRegistry implements IRegistry
{
    private function __construct()
    {
        session_start();
    }

    public function get( $key )
    {
        if ( isset( $_SESSION[__CLASS__][$key] ) ) {
            return $_SESSION[__CLASS__][$key];
        }
        return null;
    }

    public function set( $key, $val )
    {
        $_SESSION[__CLASS__][$key] = $val;
    }
}

class ApplicationRegistry implements IRegistry
{
    private $freezedir = "/tmp/data";
    private $values = array();
    private $mtimes = array();

    private function __construct()
    {
    }

    public function get( $key )
    {
        $path = $this->freezedir . DIRECTORY_SEPARATOR . $key;
        if ( file_exists( $path ) ) {
            clearstatcache();
            $mtime=filemtime( $path );
            if ( ! isset($this->mtimes[$key] ) ) { $this->mtimes[$key]=0; }
            if ( $mtime > $this->mtimes[$key] ) {
                $data = file_get_contents( $path );
                $this->mtimes[$key]=$mtime;
                return ($this->values[$key]=unserialize( $data ));
            }
        }
        if ( isset( $this->values[$key] ) ) {
            return $this->values[$key];
        }
        return null;
    }

    public function set( $key, $val )
    {
        $this->values[$key] = $val;
        $path = $this->freezedir . DIRECTORY_SEPARATOR . $key;
        file_put_contents( $path, serialize( $val ) );
        $this->mtimes[$key]=time();
    }
}

class MemApplicationRegistry implements IRegistry
{
    private $id;
    const DSN=1;

    private function __construct()
    {
        $this->id = @shm_attach(55, 10000, 0600);
        if ( ! $this->id ) {
            throw new \Exception("could not access shared memory");
        }
    }

    public function get( $key )
    {
        return shm_get_var( $this->id, $key );
    }

    public function set( $key, $val )
    {
        return shm_put_var( $this->id, $key, $val );
    }

}