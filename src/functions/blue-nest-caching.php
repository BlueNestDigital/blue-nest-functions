<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

function cacheSetDirectory($dir) {
    $GLOBALS['CACHE_DIR'] = $dir;
}

/*
 * Function takes param $callback (anonymous function), does some checking then either presents cache results
 * ($useCache) or execute and cache the $callback function.
 *
 * @param $callback
 * @param $cacheKey
 * @param $useCache
 */
function cacheEvaluate(closure $callback, $cacheKey, $useCache = false) {
    if(!isset($GLOBALS['CACHE_DIR'])) {
        throw new Exception("Must set cache directory to use this");
    }

    if(defined("CACHE_GLOBAL_USE_CACHE") && !CACHE_GLOBAL_USE_CACHE) {
        $useCache = CACHE_GLOBAL_USE_CACHE;
    }

    $filename = directoryReference($GLOBALS['CACHE_DIR'], $cacheKey);

    if($useCache) {
        if(file_exists($filename)) {
            printMsg("Reading '" . $cacheKey . "' from cache");
            $contents = file_get_contents($filename);
            return unserialize($contents);
        } else {
            printMsg("Cache file for '" . $cacheKey . "' does not exist: " . $filename);
        }
    }

    try {
        $result = $callback();
    } catch(\Exception $e) {
        printMsg(reflectDescribeClosure($callback));
        echo "cacheEvaluate(): Exception encountered in callback" . PHP_EOL;

        echo $e->getMessage() . PHP_EOL;
        echo $e->getTraceAsString() . PHP_EOL;
        throw $e;
    }
    if(CACHE_STORING_ON || $useCache) {
        if(!file_put_contents($filename, serialize($result))) {
            printMsg("Warning: could not add data to cache for key: " . $cacheKey );
        } else {
            printMsg("Cached a value for key '" . $cacheKey . "'" );
        }
    }

    return $result;
}

/*
 * function retrieveFile.  Get file or cashed version of it.
 *
 * @param $getFileCallback
 * @param $cacheKey
 */
function retrieveFile($getFileCallback, $cacheKey, $forceUseCache = false) {
    //change to root path . storage_path

    $cacheIndex = ROOT_PATH . "/" . EnvLoader::get("storage.directory") ."/". "file-cache-index.json";

    $useCache = false;
    $refreshFile = true;
    $cachedFileName = null;

    if(file_exists($cacheIndex)) {
        $indexData = fileGetJson($cacheIndex);
    } else {
        $indexData = [];
    }

    // old: defined("FILE_CACHE_ENABLED")
    if(EnvLoader::get("file-cache.enabled") || $forceUseCache) {
        // old: defined("FILE_CACHE_STALENESS_AGE_IN_MINUTES")
        $tmpFileCacheStaleness = EnvLoader::get("file-cache.staleness");
        if($tmpFileCacheStaleness) {
            if(array_key_exists($cacheKey, $indexData)) {
                $cacheData = $indexData[$cacheKey];
                $lastModified = $cacheData['modified'];
                $cachedFileName = $cacheData['filename'];
                $isLive = isset($cacheData['live']) ? $cacheData['live'] : true;

                if($forceUseCache) {
                    $useCache = true;
                    $refreshFile = false;
                } else if(!$isLive) {
                    $useCache = false;
                    $refreshFile = false;
                } else if(!file_exists($cachedFileName)) {
                    $useCache = false;
                    $refreshFile = true;
                } elseif ($tmpFileCacheStaleness == -1) {
                    $useCache = false;
                    $refreshFile = false;
                }
                else {
                    $useCache = true;
                    $expireTime = $lastModified + (60 * $tmpFileCacheStaleness);
                    $refreshFile = (time() > $expireTime);
                }

            }
        }
    }

    if(!$useCache) {
        try {
            $filename = $getFileCallback();
        } catch(\Exception $e) {
            var_dump($getFileCallback);
            throw $e;
        }
        if($refreshFile) {
            $indexData[$cacheKey] = [
                "modified" => time(),
                "filename" => $filename
            ];
            filePutJson($cacheIndex, $indexData);
        }
        printMsg("Retrieved file from SOURCE: " . $filename);
    } else {
        if($cachedFileName === null) {
            throw new \Exception("Code error");
        }
        $filename = $cachedFileName;
        printMsg("Retrieved file from CACHE: " . $filename);
        return $filename;
    }

    return $filename;
}