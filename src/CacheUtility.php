<?php
/**
 * Created by PhpStorm.
 * User: Matthias
 * Date: 24.03.2015
 * Time: 19:38
 */

namespace Kanti\Kache;


class CacheUtility
{
    protected static $log = array();

    public static function getLog(){
        return static::$log;
    }

    public static function log($name, $message)
    {
        static::$log[$name][] = $message;
    }

    /**
     * @description Returns a sanitized string, typically for URLs.
     *
     * @param string $string The string to sanitize.
     * @param bool $force_lowercase Force the string to lowercase?
     * @param bool $anal If set to *true*, will remove all non-alphanumeric characters.
     * @return string
     */
    public static function sanitize($string, $force_lowercase = true, $anal = false)
    {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ">", "..", "/", "?");
        $clean = trim(str_replace($strip, " ", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
        return ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
                mb_strtolower($clean, 'UTF-8') :
                strtolower($clean) :
            $clean;
    }

    public static function file_force_contents()
    {
        $args = func_get_args();
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $args[0]);
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        array_pop($parts);
        $directory = '';
        foreach ($parts as $part):
            $check_path = $directory . $part;
            if (is_dir($check_path . DIRECTORY_SEPARATOR) === FALSE) {
                mkdir($check_path, 0755);
            }
            $directory = $check_path . DIRECTORY_SEPARATOR;
        endforeach;
        call_user_func_array('file_put_contents', $args);
    }
}