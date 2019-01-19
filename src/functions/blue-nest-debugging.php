<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

/**
 * @deprecated
 * @param string $msg
 */
function bndDebugDie() {
    $args = func_get_args();

    $caller = findFunctionCallerLocation(__FUNCTION__);
    if(count($args)) {
        foreach($args as $i => $arg) {
            echo "Die arg #" . ($i+1) . ": " . print_r($arg, true) . PHP_EOL;
        }
    }
    die('DEBUG USE ONLY - bndDebugDie() called from: ' . $caller);
}

function bndRemoveMe($msg = null) {
    $fullMsg = "REMOVE ME";
    if($msg !== null) {
        $fullMsg = $fullMsg . ": " . $msg;
    }
    printMsg($fullMsg, true, __FUNCTION__);
}

function varDumpData($var) {
    ob_start();
    var_dump($var);
    return ob_get_clean();
}

function printR(/* func_get_args */) {
    $str = "";

    $args = func_get_args();
    foreach($args as $arg) {
        if(!empty($str)) {
            $str .=  PHP_EOL;
        }

        $str .= print_r($arg, true);
    }

    return $str;
}

function varDumpToFile($file, $var) {
    if(!file_put_contents($file, varDumpData($var))) {
        throw new \RuntimeException('Could not dump var to file: ' . $file);
    }
    echo 'Var dumped to file: ' . $file . PHP_EOL;
}

/**
 * @deprecated
 *
 * @param $rootDir
 * @param $requiredFilename
 * @return mixed
 * @throws Exception
 */
function lazyRequire($rootDir, $requiredFilename) {
    echo 'Searching: ' . $rootDir . PHP_EOL;
    $iterator = new RecursiveDirectoryIterator($rootDir);
    foreach(new RecursiveIteratorIterator($iterator) as $file) {
        $filename = basename($file);
        if($filename === $requiredFilename) {
            return require $file;
        }
    }

    throw new Exception('Could not find file to require: ' . $requiredFilename);
}

function showNewLines($str) {
    $str = str_replace("\n", '<NL>', $str);
    $str = str_replace("\r", '<CR>', $str);
    return $str;
}

function printMemoryStats() {
    if(defined("PRINT_MEMORY_STATS") && PRINT_MEMORY_STATS) {
        $memStats = array(
            'peak' => (memory_get_peak_usage() / 1e6) . " MB",
            'usage' => (memory_get_usage(true)  / 1e6) . " MB",
            'limit (ini)' => ini_get('memory_limit')
        );

        printMsg('Memory stats: ' . jsonPretty($memStats));
    }
}

function debugVar($var) {
    return '"' . varInspect($var) . '"';
}

function getAsString($val, $return = false) {
    return printAsString($val, true);
}

function printAsString($val, $return = false) {
    if(is_string($val)) {
        $output = $val;
    } else {
        $output = varInspect($val);
    }

    if($return) {
        return $output;
    } else {
        echo $val;
    }
}

function varInspect($var) {
    if(is_null($var)) {
        return '<NULL>';
    } else if(is_bool($var)) {
        return $var ? '<True>' : '<False>';
    } else if($var === "") {
        return "<empty string>";
    } else if(is_array($var)) {
        return print_r($var, true);
    } else if(is_object($var)) {
        return print_r($var, true);
    } else {
        return print_r($var, true);
    }
}

function toStringVar($var) {
    if(method_exists($var, 'toString')) {
        return $var->toString();
    } else {
        return (string) $var;
    }
}

/**
 * @param $obj
 * @throws ReflectionException
 */
function printProperties($obj) {
    echo get_class($obj) . " " . spl_object_hash($obj) . PHP_EOL;
    $class = new ReflectionClass(get_class($obj));
    $methods = $class->getMethods();

    /** @var ReflectionMethod $method */
    foreach($methods as $method) {
        if(strBegins($method->getName(), "get")) {
            $val = $method->invoke($obj);
            if(is_array($val) || is_object($val)) {
                echo $method->getName() . " = " . print_r($val, true) . PHP_EOL;
            } else {
                echo $method->getName() . " = " . $val . PHP_EOL;
            }
        }
    }
    echo PHP_EOL . PHP_EOL;
}

function printMarquee($msg) {
    printMsg(str_repeat("-", 20) . $msg . str_repeat("-", 20));
}

function bndDebug() {
    $callLocation = findFunctionCallerLocation(__FUNCTION__);
    do {
        echo "Stopped at: " . $callLocation . PHP_EOL;
        $data = readline("What do you want to eval:? " . PHP_EOL);

        if(trim($data) === "") {
            return;
        } else if(trim($data) === "{") {
            $brackets = 1;
            do {
                $otherData = readline();

                $openBracketCount = substr_count($otherData, "{");
                $closeBracketCount = substr_count($otherData, "}");

                $brackets += $openBracketCount;
                $brackets -= $closeBracketCount;

                $data .= $otherData . PHP_EOL;
            } while($brackets !== 0);
        }

        if(substr($data, -1) !== ";") {
            $data .= ";";
        }

        echo print_r($data, true) . PHP_EOL;

        try {
            echo PHP_EOL;
            eval($data);
            echo PHP_EOL;
        } catch(Exception $e) {
            echo $e;
        } catch(Error $err) {
            echo $err;
        }
    } while(!empty($data));
}