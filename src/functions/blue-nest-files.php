<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

function fileGetContents($filename) {
    if(!file_exists($filename)) {
        throw new RuntimeException("File does not exist: " . $filename);
    }

    $contents = file_get_contents($filename);

    if($contents === false) {
        throw new RuntimeException("file_get_contents failed for file: " . $filename);
    }

    return $contents;
}

function filePutContents($filename, $str, $flags = 0, $context = null) {
    if(!file_exists($filename)) {
        throw new RuntimeException("File does not exist: " . $filename);
    }

    $result = file_put_contents($filename, $str, $flags, $context);

    if($result === false) {
        throw new RuntimeException("fileput_contents failed for file: " . $filename);
    }
}

function serializeToFile($filename, $data) {
    $serializedData = serialize($data);

    filePutContents($filename, $serializedData);
}

function unserializeFromFile($filename) {
    $contents = fileGetContents($filename);

    $unserializedData = unserialize($contents);

    if($unserializedData === false) {
        throw new RuntimeException("Could not unserialize data in file: " . $filename);
    }

    return $unserializedData;
}

function filePut($location, $data, $permissions = 0775, $verbose = false, $directorySeparator = "/") {
    $dir = dirname($location);
    $dirs = explode("/", $dir);
    /*
        if(!is_writable($dir)) {
            die("filePut: Error - Directory is not writable: $dir");
        }
    */
    $curDir = "";
    while(count($dirs)) {
        $curDir .= $directorySeparator . array_shift($dirs);
        if(!is_dir($curDir)) {
            try {
                if(!mkdir($curDir, $permissions)) {
                    $cwd = getcwd();
                    throw new Exception("filePut: Could not make directory (zero returned): " . $curDir . " in " . $cwd);
                } else {
                    if($verbose) {
                        printMsg("Created directory for filePut(): " . $curDir);
                    }
                }
            } catch(Exception $e) {
                $cwd = getcwd();
                throw new Exception("filePut: Could not make directory (exception): " . $curDir . " in " . $cwd);
            }
        }
    }

    return file_put_contents($location, $data);
}

function fileSplit($filename, $rowsPerFile, $numHeaderRows = 1) {
    $inputFileHandle = fopen($filename, "r");
    $pathInfo = pathinfo($filename);
    $currentOutputFileNumber = 0;

    $initialized = false;
    $outputFileHandle = null;
    $rowCount = 0;
    $totalRowCount = 0;
    $headerRows = '';

    $actualRowsPerFile = $rowsPerFile - $numHeaderRows;

    $newFilenames = array();
    while (!feof($inputFileHandle)) {
        if($totalRowCount < $numHeaderRows) {
            $headerRows .= fgets($inputFileHandle);
        } else {
            if(!$initialized || ($rowCount >= $actualRowsPerFile)) {
                $initialized = true;
                $currentOutputFileNumber += 1;
                $rowCount = 0;

                $currentOutputFileName = '';
                if($pathInfo['dirname'] !== '') {
                    $currentOutputFileName = $pathInfo['dirname'] . '/';
                }

                $currentOutputFileName .= $pathInfo['filename'] . '-' . $currentOutputFileNumber . '.' . $pathInfo['extension'];
                printMsg('Will split to file: ' . $currentOutputFileName);
                $outputFileHandle = fopen($currentOutputFileName, 'w');
                fwrite($outputFileHandle, $headerRows);
                $newFilenames[] = $currentOutputFileName;
            }

            $data = fgets($inputFileHandle);
            fwrite($outputFileHandle, $data);
            $rowCount += 1;
        }
        $totalRowCount += 1;
    }

    fclose($inputFileHandle);

    if($initialized) {
        fclose($outputFileHandle);
    }

    if($totalRowCount === 0) {
        echo "fileSplit(): Warning: no rows in input file" . PHP_EOL;
    }

    return $newFilenames;
}



function copyFile($src, $dest, $overwrite = false) {
    if(!$overwrite && file_exists($dest)) {
        throw new \Exception('copyFile(): Destination file exists: ' . $dest);
    }

    if(!file_exists($src)) {
        throw new \Exception('copyFile(): Source file does not exist: ' . $src);
    }

    if(!copy($src, $dest)) {
        throw new \Exception('copyFile(): Failed copying file from ' . $src . ' to ' . $dest);
    }

    printMsg('copyFile(): Copied file: "' . $src . '" to "' . $dest . '"');
}

function moveFile($src, $dest, $overwrite = false) {
    if(!$overwrite && file_exists($dest)) {
        throw new \RuntimeException('Destination file exists: ' . $dest);
    }

    if(!file_exists($src)) {
        throw new \RuntimeException('moveFile(): Source file does not exist: ' . $src);
    }

    if(!rename($src, $dest)) {
        throw new \RuntimeException('moveFile(): Failed moving file from ' . $src . ' to ' . $dest);
    }

    printMsg('moveFile(): Moved file: "' . $src . '" to "' . $dest . '"');
}

function filePathAssertExists($path) {
    if(!file_exists($path)) {
        throw new \Exception('Path does not exist: ' . $path);
    }
    return $path;
}

function makeDirectory($dir, $directorySeparator = "/", $permissions = 0775, $verbose = false) {
    $dirs = explode("/", $dir);

    $curDir = "";
    while(count($dirs)) {
        $curDir .= $directorySeparator . array_shift($dirs);
        if(!is_dir($curDir)) {

            if(file_exists($curDir)) {
                throw new \Exception($curDir . " already exists but is no a directory");
            }

            try {
                if(!mkdir($curDir, $permissions)) {
                    $cwd = getcwd();
                    throw new Exception("filePut: Could not make directory (zero returned): " . $curDir . " in " . $cwd);
                } else {
                    if($verbose) {
                        echo "Created directory for filePut(): " . $curDir . "<br>";
                    }
                }
            } catch(Exception $e) {
                $cwd = getcwd();
                throw new Exception("filePut: Could not make directory (exception): " . $curDir . " in " . $cwd);
            }
        }
    }
}

function replaceDirectorySeparators($str, $replaceWith = "_") {
    return str_replace(["/", "\\"], "_", $str);
}

function directoryReference($args) {
    $args = func_get_args();
    return implode("\\", $args);
}