<?php

namespace Knight;

class Config {
    private static $config;

    public static function loadConfig()
    {
        $localConfig = (is_file(getcwd() . '/knight.config.php')) ? include(getcwd() . '/knight.config.php') : [];
        $defaultConfig = include(realpath(dirname(__FILE__)).'/default.config.php');
        self::$config = array_merge($defaultConfig, $localConfig);
    }

    public static function get($name)
    {
        return isset(self::$config[$name]) ? self::$config[$name] : null;
    }

    public static function set($name, $value)
    {
        self::$config[$name] = $value;
        return $value;
    }

    public static function __callStatic($name, $args)
    {
        $isGet = strpos($name, 'get') !== false;
        $isSet = strpos($name, 'set') !== false;

        if (!$isGet && !$isSet) throw new \Exception('Could not find static method ' . $name . ' in class Config.'.PHP_EOL);

        $name = preg_replace('|^get|', '', $name);
        $name = preg_replace('|^set|', '', $name);
        $name = App::from_camel_case($name);

        if ($isGet) return self::get($name); 
        else return self::set($name, $args[0]);
    }
}
