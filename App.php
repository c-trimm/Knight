<?php

namespace Knight;

class App {
    public static function run($watch = false)
    {
        Generator::generate();
        if ($watch) self::watch();
    }

    protected static function watch() 
    {
        $dirs = [
            App::getDir().Config::getLayoutsDir() => null,
            App::getDir().Config::getPagesDir()   => null,
            App::getDir().Config::getEntriesDir() => null,
            App::getDir().Config::getScriptsDir() => null,
            App::getDir().Config::getStylesDir()  => null,
            App::getDir().Config::getImagesDir()  => null,
            App::getDir().Config::getStaticDir()  => null,
        ];

        foreach($dirs as $dir => $hash) {
            if (!is_dir($dir)) unset($dirs[$dir]);
            else $dirs[$dir] = App::md5_dir($dir);
        }

        while(true) {
            foreach($dirs as $dir => $hash) {
                $dirs[$dir] = App::md5_dir($dir);
                if ($dirs[$dir] !== $hash) Generator::generate();
            }
        }
    }

    /*********************/
    /***** UTILITIES *****/
    /*********************/
    public static function out($msg)
    {
        echo $msg . PHP_EOL;
    }

    public static function bold($msg)
    {
        return "\033[1m".$msg."\033[0m";
    }

    public static function del($dir)
    {
        if (is_file($dir)) {
            return unlink($dir);
        }

        if (!is_dir($dir)) {
            return true;
        }

        $files = array_diff(scandir($dir), array('.','..'));

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? App::del("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @param       int      $permissions New folder creation permissions
     * @return      bool     Returns true on success, false on failure
     */
    public static function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            App::xcopy("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();
        return true;
    }

    // Get md5 hash of directroy
    public static function md5_dir($dir)
    {
        if (!is_dir($dir)) return is_file($dir) ? md5_file($file) : false;

        $filemd5s = array();
        $d = dir($dir);

        while (false !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') continue;
            $entry = $dir.'/'.$entry;
            $filemd5s[] = is_dir($entry) ? App::md5_dir($entry) : md5_file($entry);
        }

        $d->close();
        return md5(implode('', $filemd5s));
    }

    public static function from_camel_case($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    public static function getDir() 
    {
        return getcwd() . DIRECTORY_SEPARATOR;
    }

    public static function ensureDir($dir)
    {
        if (!is_dir($dir)) mkdir($dir);
    }
}
