<?php
/**
 * Created by PhpStorm.
 * User: Matthias
 * Date: 24.03.2015
 * Time: 19:35
 */

namespace Kanti\Kache;


class Cache
{
    public static $path = '';

    protected $identifier = '';
    protected $fileName = '';
    protected $first = 0;
    protected $second = 0;

    protected $functions = array();

    public function __construct($identifier, $first = 60, $second = 43200)
    {
        $this->identifier = $identifier;
        static::$path = rtrim(static::$path, "/") . "/";
        if (static::$path == '/') {
            static::$path = '';
        }
        $this->fileName = static::$path . CacheUtility::sanitize($identifier) . ".cache.json";
        $this->first = $first;
        $this->second = $second;
        $this->functions['onNoCache'] = function () {
            throw new \Exception("no onNoCache Callback defined");
        };
        $this->functions['onErrorAndNoCache'] = function (\Exception $e) {
            throw $e;
        };
    }


    public function onNoCache($function)
    {
        $this->functions['onNoCache'] = $function;
    }

    public function onErrorAndNoCache($function)
    {
        $this->functions['onErrorAndNoCache'] = $function;
    }

    public function process($function)
    {
        $this->functions['process'] = $function;
    }

    public function get($force = false, $run = true)
    {
        if (!isset($this->functions['process'])) {
            throw new \Exception("no process Function Defined");
        }

        if (file_exists($this->fileName)) {
            $time = filemtime($this->fileName);
            $first = time() - $this->first;
            $second = time() - $this->second;
            if (($time > $first) || (($time > $second) && !$force)) {
                CacheUtility::log($this->identifier, "retrieve Data from Cache");
                $data = json_decode(file_get_contents($this->fileName));
                if (json_last_error() == JSON_ERROR_NONE) {
                    return $data;
                }
            }
        }
        try {
            if (!$run) {
                CacheUtility::log($this->identifier, "run onNoCache after no run");
                $call = $this->functions['onNoCache'];
                return $call();
            }
            CacheUtility::log($this->identifier, "run process");
            $call = $this->functions['process'];
            $data = $call();
            $dataString = json_encode($data);
            CacheUtility::file_force_contents($this->fileName, $dataString);
            CacheUtility::log($this->identifier, "safe Cache");
            return json_decode($dataString);
        } catch (\Exception $e) {
            if (file_exists($this->fileName)) {
                CacheUtility::log($this->identifier, "retrieve Data from Cache (after Exception)");
                $data = json_decode(file_get_contents($this->fileName));
                if (json_last_error() == JSON_ERROR_NONE) {
                    return $data;
                }
            }
            CacheUtility::log($this->identifier, "run onErrorAndNoCache");
            $call = $this->functions['onErrorAndNoCache'];
            if ($result = $call($e)) {
                return $result;
            }
        }
        CacheUtility::log($this->identifier, "run onNoCache");
        $call = $this->functions['onNoCache'];
        return $call();
    }

    protected function processGet($function, $force = false, $run = true)
    {
    }
}