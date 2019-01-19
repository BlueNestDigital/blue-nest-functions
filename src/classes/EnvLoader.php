<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

class EnvLoader {
    const ENV_FILE_NAME_PATTERN = "_env.{environment}";
    const ENVIRONMENT_SETTING_FILE = "_env_current";
    const ENV_NAME_MAX_STR_LENGTH = 20;

    private static $env = null;
    private static $envMap = null;

    public static function setEnvironment($env) {
        if(EnvLoader::$env !== null) {
            throw new \Exception("Only allowed to set environment once");
        }
        EnvLoader::$env = $env;
    }

    public static function getEnvironmentFileName($env) {
        $fileName = trim(str_replace("{environment}", $env, EnvLoader::ENV_FILE_NAME_PATTERN));

        if(defined("ROOT_PATH")) {
            $fileName = ROOT_PATH . "/" . $fileName;
        }
        return $fileName;
    }

    /**
     * Get the name of the current environment (e.g. 'local') from the "environment setting" file
     *
     * @return string
     * @throws Exception
     */
    public static function getCurrentEnvironmentFromFile() {
        $file = EnvLoader::ENVIRONMENT_SETTING_FILE;
        if(defined("ROOT_PATH")) {
            $file = ROOT_PATH . "/" . $file;
        }

        if(!file_exists($file)) {
            throw new \Exception("Please create a file to set current environment: " . EnvLoader::ENVIRONMENT_SETTING_FILE);
        }

        $env = file_get_contents($file);

        if(empty($env)) {
            throw new \Exception("Environment file is empty: " . $file);
        }

        $env = trim($env);

        if(strlen($env) > EnvLoader::ENV_NAME_MAX_STR_LENGTH) {
            throw new \Exception("Environment name must be less that " . EnvLoader::ENV_NAME_MAX_STR_LENGTH . " characters");
        }

        return $env;
    }

    public static function getEnv() {
        return EnvLoader::$envMap;
    }

    public static function get($key, $default = BND_DEFAULT_VALUE) {
        try {
            $val = EnvLoader::getDeepKey(EnvLoader::$envMap, $key, $default);
            if(is_string($val)) {
                $val = static::convertStringIfBoolean($val);
            }
        } catch(\Exception $e) {
            throw new \RuntimeException("Could not load config value for key '" . $key . "'", 0, $e);
        }
        return $val;
    }

    private static function convertStringIfBoolean($val) {
        if(strtolower($val) === "true") {
            return true;
        } else if(strtolower($val) === "false") {
            return false;
        }

       return $val;
    }

    private static function getDeepKey($map, $key, $default = BND_DEFAULT_VALUE) {
        $fullKey = $key;
        $keys = explode(".", $key);

        $key = array_shift($keys);

        if(!is_array($map)) {
            throw new \RuntimeException("Configuration key is not an array: " . $key . " (" . $fullKey . ")");
        }

        if(!array_key_exists($key, $map)) {
            if($default !== BND_DEFAULT_VALUE) {
                return $default;
            } else {
                throw new \RuntimeException("Configuration value missing for key: '" . $fullKey . "'");
            }
        }

        $val = $map[$key];

        foreach($keys as $key) {
            if(!is_array($val)) {
                throw new \RuntimeException("Configuration key is not an array: " . $key . " (" . $fullKey . ")");
            }

            if(!array_key_exists($key, $val)) {
                if($default !== BND_DEFAULT_VALUE) {
                    return $default;
                } else {
                    throw new \RuntimeException("Configuration value missing for key: '" . $fullKey . "'");
                }            }
            $val = $val[$key];
        }

        return $val;
    }

    public static function loadEnvironment() {
        $envData = getenv("ENVLOADER_CONFIGURATION");

        $env = EnvLoader::getCurrentEnvironmentFromFile();
        echo "Current environment set to: " . $env . PHP_EOL;
        EnvLoader::setEnvironment($env);

        if($envData !== false) {
            echo "Loading configuration from environment variable" . PHP_EOL;
            $arr = EnvLoader::loadCurrentEnvironmentFromJson($envData);
        } else {
            echo "Loading configuration from file" . PHP_EOL;
            $arr = EnvLoader::loadEnvironmentFromFile();
        }

        static::performEnvironmentVariableReplacements($arr);

        EnvLoader::$envMap = $arr;

        echo "Done loading '" . $env . "' environment configuration from file" . PHP_EOL;
    }

    private static function performEnvironmentVariableReplacements(&$arr) {
        foreach($arr as $key => &$val) {
            if(is_array($val)) {
                static::performEnvironmentVariableReplacements($val);
            } else if(is_string($val) && preg_match("`\[ENV:([A-Za-z0-9_]+)(?:\|(.+))?\]`", $val, $matches)) {
                $hasDefaultValue = false;

                if(count($matches) === 2) {
                    $environmentVariableName = $matches[1];
                } else if(count($matches) === 3) {
                    $environmentVariableName = $matches[1];
                    $defaultValue = $matches[2];
                    $hasDefaultValue = true;
                } else {
                    throw new RuntimeException("Unexpected match count");
                }

                $envValue = getenv($environmentVariableName);

                if($envValue === false) {
                    if($hasDefaultValue) {
                        $envValue = $defaultValue;
                    } else {
                        throw new RuntimeException("Key '" . $key . "' environment variable '" . $environmentVariableName . "' is not set (no default specified)");
                    }
                }

                $val = $envValue;
            }
        }
    }

    /**
     * Parse json to array, stripping out comments
     *
     * @param $jsonString
     * @return mixed
     * @throws Exception
     */
    public static function loadCurrentEnvironmentFromJson($jsonString) {
        $jsonString = trim($jsonString);
        $lines = explode(PHP_EOL, $jsonString);

        $contentLines = [];
        foreach($lines as $line) {
            $line = trim($line);
            if(strpos($line, "//") === 0) {
                continue;
            }
            $contentLines[] = $line;
        }

        $contentJson = implode(PHP_EOL, $contentLines);

        $arr = json_decode($contentJson, true);

        if($arr === null) {
            throw new \RuntimeException("Error parsing environment as json: " . print_r($jsonString) . PHP_EOL . "JSON error=" . json_last_error_msg());
        }

        return $arr;
    }

    public static function loadEnvironmentFromFile() {
        $filename = EnvLoader::getEnvironmentFileName(EnvLoader::$env);

        if(!file_exists($filename)) {
            throw new \Exception("Missing environment file: '" . $filename . "'");
        }

        $fileContents = file_get_contents($filename);

        return static::loadCurrentEnvironmentFromJson($fileContents);
    }
}