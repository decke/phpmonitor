<?php

namespace PhpMonitor;


class Config
{
    protected static $configfile = 'monitor.json';
    protected static $data = null;

    public static function load($file = null)
    {
        if($file === null)
            $file = self::$configfile;

        if(!file_exists($file) || !is_readable($file))
            trigger_error(E_USER_ERROR, "Config file ".$file." not found!");

        self::$data = json_decode(file_get_contents($file), true);
        return true;
    }

    public static function get($property, $defaultvalue = null)
    {
        if(self::$data === null)
            self::load();

        if(!isset(self::$data['config'][$property]))
            return $defaultvalue;

        return self::$data['config'][$property];
    }

    public static function getChecks()
    {
        if(self::$data === null)
            self::load();

        return self::$data['checks'];
    }
}

