<?php
require "vendor/autoload.php";

use \Kanti\Kache\Cache;
use \Kanti\Kache\CacheUtility;

Cache::$path = 'cache/';
$cache = new Cache('test', 5, 20);
$cache->process(function () {
    if (rand(0, 4)) {
        sleep(2);
        return 'newData';
    }
    sleep(1);
    throw new \Exception("fuck");
});
$cache->onErrorAndNoCache(function (\Exception $e) {
    //ignore Exception
    return $e->getMessage();
});
$cache->onNoCache(function () {
    return 'lol';
});
echo $cache->get();

echo "<pre>";
var_dump(CacheUtility::getLog());
echo "</pre>";