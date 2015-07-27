<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 17/07/2015
 * Time: 10:35
 */

namespace Andersonef\ApiImplementation\Contracts;


trait SingletonTrait
{
    private static $instance;

    public static function getInstance()
    {
        if(!self::$instance) self::$instance = app(__CLASS__);
        return self::$instance;
    }
}