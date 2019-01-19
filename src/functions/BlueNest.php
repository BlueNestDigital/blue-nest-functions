<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

define('REGEX_NON_STANDARD_CHARACTERS', "#[^\x{0020}-\x{00BF}]#u");
define('UTF8_BOM', "\xEF\xBB\xBF");
define('PRINT_MESSAGES_WITH_FILE_LOCATION', true);
define('EMPTY_CSV_ROW_CONTENT', '');

require_once __DIR__ . '/blue-nest-arrays.php';
require_once __DIR__ . '/blue-nest-dates.php';
require_once __DIR__ . '/blue-nest-debugging.php';
require_once __DIR__ . '/blue-nest-files.php';
require_once __DIR__ . '/blue-nest-input.php';
require_once __DIR__ . '/blue-nest-json.php';
require_once __DIR__ . '/blue-nest-logging.php';
require_once __DIR__ . '/blue-nest-reflection.php';
require_once __DIR__ . '/blue-nest-strings.php';
require_once __DIR__ . '/blue-nest-urls.php';
require_once __DIR__ . '/blue-nest-variables.php';
require_once __DIR__ . '/blue-nest-caching.php';
require_once __DIR__ . '/blue-nest-math.php';
require_once __DIR__ . '/blue-nest-json-files.php';
require_once __DIR__ . '/blue-nest-mail.php';
require_once __DIR__ . '/blue-nest-zip-files.php';

function bndEnv($key) {
    return EnvLoader::get($key);
}

function projectPath($pathType, $file) {
    return ROOT_PATH . "/" . EnvLoader::get($pathType . ".directory") . $file;
}

/**
 * @param $filename
 * @param bool $fileHasHeaders
 * @param bool $useIndices
 * @param bool $verbose
 * @param null $maxLineLength
 * @param string $enclosure
 * @param string $escape
 * @param bool $simpleMode
 * @param bool $endDelimited
 * @return \CsvRow[]
 */
function csvFileToTable($filename,
                        $fileHasHeaders = true,
                        $useIndices = false,
                        $verbose = true,
                        $maxLineLength = null,
                        $enclosure = "\"",
                        $escape = "\"",
                        $simpleMode = true,
                        $endDelimited = false
) {
    $fileData = csvFileRead($filename, $fileHasHeaders, $useIndices, $verbose, $maxLineLength, ",", $enclosure, $escape, $simpleMode, $endDelimited);
    return $fileData['data'];
}

/**
 * @param $str
 * @param bool $fileHasHeaders
 * @param bool $useIndices
 * @param bool $verbose
 * @param null $maxLineLength
 * @param string $enclosure
 * @param string $escape
 * @param bool $simpleMode
 * @param bool $endDelimited
 * @return \CsvRow[]
 */
function csvStringToTable($str,
                        $fileHasHeaders = true,
                        $useIndices = false,
                        $verbose = true,
                        $maxLineLength = null,
                        $enclosure = "\"",
                        $escape = "\"",
                        $simpleMode = true,
                        $endDelimited = false
) {
    $fileData = csvStringRead($str, $fileHasHeaders, $useIndices, $verbose, $maxLineLength, ",", $enclosure, $escape, $simpleMode, $endDelimited);
    return $fileData['data'];
}

function tsvFileToTable($filename,
                        $fileHasHeaders = true,
                        $useIndices = false,
                        $verbose = true,
                        $maxLineLength = null,
                        $enclosure = null,
                        $escape = null,
                        $simpleMode = true,
                        $endDelimited = false
) {
    $fileData = csvFileRead($filename, $fileHasHeaders, $useIndices, $verbose, $maxLineLength, "\t", $enclosure, $escape, $simpleMode, $endDelimited);
    return $fileData['data'];
}

function showWhitespace($str) {
    $str = str_replace("\n", "[NL]", $str);
    $str = str_replace("\r", "[CR]", $str);
    $str = str_replace(" ", "[SPACE]", $str);
    return $str;
}

// BND TODO ESCAPE CHANGED
function parseCsvLine($str, $separatorChar = ",", $enclosureChar = '"', $escapeChar = "\"", $simpleMode = false) {
    //$str .= PHP_EOL;

    $data = [];
    $currentCellData = '';
    $insideEnclosure = false;
    $justEndedEnclosure = false;

    $lengthStr = strlen($str);

    $previousCharacter = null;

    try {

        for($i=0; $i < $lengthStr; $i++) {
            $character = $str[$i];
/*
            if($justEndedEnclosure && $enclosureChar !== null && ($character !== $separatorChar && $character !== PHP_EOL)) {
                $debugInfo = array(
                    'character' => showWhitespace($character),
                    'position' => $i,
                    'current portion of string: ' => $currentCellData,
                    'full line' => $str,
                );
                throw new \Exception("Malformed delimited line: Expecting separator or end of line, debug data: " . print_r($debugInfo, true));
            }
*/
            $isEscape = $character === $escapeChar;

                // BND REVISIT empty cell handling ""

            if($insideEnclosure && $isEscape && $escapeChar === $enclosureChar) {
                if($i >= $lengthStr - 1) {
                    $nextCharacter = NULL;
                } else {
                    $nextCharacter = $str[$i + 1];
                }

                if($nextCharacter === $escapeChar) {
                    $currentCellData .= $escapeChar;
                    $i += 1;
                    continue;
                }
            }

            if($insideEnclosure && $isEscape) {
                if($i >= $lengthStr - 1) {
                    $nextCharacter = NULL;
                } else {
                    $nextCharacter = $str[$i + 1];
                }

                if(($escapeChar !== $enclosureChar)
                    || (($nextCharacter !== $separatorChar && $nextCharacter !== null && $nextCharacter !== PHP_EOL))
                    && ($previousCharacter !== $separatorChar && $previousCharacter !== null && $previousCharacter !== PHP_EOL)) {
                    $currentCellData .= $nextCharacter;
                    $i += 1;
                    continue;
                }
            }

            if($character === $separatorChar && !$insideEnclosure) {
                if($simpleMode) {
                    $data[] = $currentCellData;
                } else {
                    $currentData = [
                        "data" => $currentCellData,
                        "enclosed" => $justEndedEnclosure
                    ];
                    $data[] = $currentData;
                }

                $currentCellData = '';
                $justEndedEnclosure = false;
            } else if($character === $enclosureChar && $enclosureChar !== null) {
                if($insideEnclosure) {
                    $justEndedEnclosure = true;
                }
                $insideEnclosure = !$insideEnclosure;
            } else {
                $currentCellData .= $character;
            }
        }
    } catch(Exception $e) {
        printMsg("parseCsvLine(): Exception processing: " . print_r($str, true));
        throw $e;
    }

    //$data[] = ["data" => $currentCellData, "enclosed" => $justEndedEnclosure];
    if($simpleMode) {
        $data[] = $currentCellData;
    } else {
        $currentData = [
            "data" => $currentCellData,
            "enclosed" => $justEndedEnclosure
        ];
        $data[] = $currentData;
    }

    return $data;
}

function fGetAllCsvLineFromFile($filename, $separator, $enclosure = "\"", $escape = '"', $simpleMode = false) {
    $text = file_get_contents($filename);

    if(strBegins($text, UTF8_BOM)) {
        $text = removeBom($text);
        echo 'UTF8 file detected' . PHP_EOL;
    }

    $lineEndings = ["\r\n", "\n"];
    foreach($lineEndings as $lineEnding) {
        if(stringContains($text, $lineEnding)) {
            break;
        }
    }

    $lines = explode($lineEnding, $text);

    //$parsedLines = new SplFixedArray(count($lines));
    $parsedLines = new SplFixedArray(count($lines));

    foreach($lines as $i=>$line) {
/*
        if(substr($line, -1) === PHP_EOL) {
            $line = rtrim($line); // BND TODO SUBSTRING?
        }
*/
        $parsedLines[$i] = parseCsvLine($line, $separator, $enclosure, $escape, $simpleMode);
    }

    return $parsedLines;
}

function fGetCsvLineFromFile($handle, $separator, $enclosure = "\"", $escape = '"', $simpleMode = true, &$rawLineData = null) {
    $line = fgets($handle);

    if($rawLineData !== null) {
        $rawLineData = $line;
    }

    if($line === false) {
        return false;
    }

    if(PHP_FGET_NEWLINE !== null) {
        if(substr($line, -1 * strlen(PHP_FGET_NEWLINE)) === PHP_FGET_NEWLINE) {
            $line = substr($line, 0, strlen($line) - strlen(PHP_FGET_NEWLINE));
        }
    }

    if(trim($line) === "") {
        return false;
    }

    return parseCsvLine($line, $separator, $enclosure, $escape, $simpleMode);
}

function fileHandleLineCount($handle) {
    $count = 0;
    while(!feof($handle)){
        $line = fgets($handle);
        $count += 1;
    }
    rewind($handle);
    return $count;
}

function csvFileRead($filename,
                        $headers = true,
                        $useIndices = false,
                        $verbose = true,
                        $maxLineLength = null,
                        $delimiter = ",",
                        $enclosure = "\"",
                        $escape = "\"",
                        $simpleMode = true,
                        $endDelimited = false // if we should NOT read data after the last delimiter (similar to CSV format)
) {
	if(!file_exists($filename)) {
        throw new RuntimeException(__FUNCTION__ ."(): " . "File does not exist: " . $filename);
    }

    if (($handle = fopen($filename, "r")) !== false) {
        $data = csvFileReadHelper($handle, $headers, $useIndices, $verbose, $maxLineLength, $delimiter, $enclosure, $escape, $simpleMode, $endDelimited);
    } else {
        throw new \RuntimeException('File not found or not readable: ' . $filename);
    }

    fclose($handle);

    return $data;
}

function csvStringRead($str,
                        $headers = true,
                        $useIndices = false,
                        $verbose = true,
                        $maxLineLength = null,
                        $delimiter = ",",
                        $enclosure = "\"",
                        $escape = "\"",
                        $simpleMode = true,
                        $endDelimited = false // if we should NOT read data after the last delimiter (similar to CSV format)
) {
    $strArray = explode(PHP_EOL, $str);
    $data = csvFileReadHelper($strArray, $headers, $useIndices, $verbose, $maxLineLength, $delimiter, $enclosure, $escape, $simpleMode, $endDelimited);
    return $data;
}

function csvFileReadHelper($handle,
                        $headers = true,
                        $useIndices = false,
                        $verbose = true,
                        $maxLineLength = null,
                        $delimiter = ",",
                        $enclosure = "\"",
                        $escape = "\"",
                        $simpleMode = true,
                        $endDelimited = false // if we should NOT read data after the last delimiter (similar to CSV format)
) {
    $curRowNumber = 0;

    if(is_resource($handle)) {
        $readLineCallback = function ($handle, $delimiter, $enclosure, $escape) {
            return fgetcsv($handle, null, $delimiter, $enclosure, $escape);
        };
    } else {
        $readLineCallback = function ($handle, $delimiter, $enclosure, $escape) use (&$curRowNumber) {
            if(count($handle) <= $curRowNumber) {
                return false;
            }
            return str_getcsv($handle[$curRowNumber], $delimiter, $enclosure, $escape);
        };
    }

    /*
    $res = ini_set("auto_detect_line_endings", "1");
    if($res === false) {
        throw new \Exception("Could not ini_set() auto_detect_line_endings: " . debugVar($res));
    }*/

    if(defined('CSV_FILE_TO_TABLE_INVALID_CHAR_REPLACE_WITH') && CSV_FILE_TO_TABLE_INVALID_CHAR_REPLACE_WITH !== false) {
        $characterReplacement = CSV_FILE_TO_TABLE_INVALID_CHAR_REPLACE_WITH;
        $performCharacterReplacement = true;
    } else {
        $performCharacterReplacement = false;
    }

    $fileHasHeaders = ($headers === true); // BND TODO cleanup?

    $allRowsData = [];
    printMsg("Done reading and parsing file data");

    if($verbose) {
        //printMsg('Reading CSV file "' . (string) $handle . '"');
    }
    // TODO remove simple mode
    while (($data = $readLineCallback($handle, $delimiter, $enclosure, $escape))) {
        try {
            $curRowNumber++;

            $numFields = count($data);
            if($endDelimited) {
                $numFields -= 1;
            }

            if($performCharacterReplacement) {
                printMsg("Replacing characters");
                array_walk($data, function (&$val) use ($characterReplacement) {
                    $val = preg_replace(REGEX_NON_STANDARD_CHARACTERS, $characterReplacement, $val);
                });
            }

            if($curRowNumber === 1) {
                if($headers === true) {
                    foreach($data as &$item) {
                        if(strBegins($item, UTF8_BOM)) {
                            $item = removeBom($item);
                            printMsg('UTF8 file detected');
                        }
                    }
                    $headers = $data;
                }

                $headersMap = [];
                foreach($headers as $header) {
                    $headersMap[$header] = $header;
                }
            } else {

                $thisRowData = [];
                for($c = 0; $c < $numFields; $c++) {
                    $val = &$data[$c];

                    if($val === EMPTY_CSV_ROW_CONTENT) {
                        continue;
                    }

                    $colName = $headers[$c];

                    //if($headers !== null && !$useIndices) {
                    $thisRowData[$colName] = $val;
                    //} else {
                    //  $thisRowData[$c] = $val;
                    //}
                }

                $thisRowData = array_filter($thisRowData, function ($item) use ($simpleMode) {
                    if($simpleMode) {
                        return $item !== EMPTY_CSV_ROW_CONTENT;
                    } else {
                        return $item["data"] !== EMPTY_CSV_ROW_CONTENT;
                    }
                });

                $csvRow = new CsvRow($thisRowData, $headersMap);
                // BND TODO
                //$allRowsData[$curRowNumber - 1] = $csvRow;
                $allRowsData[] = $csvRow;
            }

            $data = null;
        } catch(Exception $e) {
            var_dump($data);
            throw $e;
        }
    }

    if($verbose) {
        printMsg('Read ' . count($allRowsData) . ' rows from CSV file');
        printMemoryStats();
    }

    return array(
        'headers' => $headers,
        'data' => $allRowsData
    );
}

function getSizeTableNestedRow($row) {
    if(gettype($row) === "array") {
        return count($row);
    } else {
        return count(array_keys($row));
    }
}

function maxRowLength($table) {
    $max = 0;
    array_walk($table, function($nestedRow) use (&$max) {
        $max = max($max, getSizeTableNestedRow($nestedRow));
    });
    return $max;
}

function csvParseFromString($csvString) {
    $csv = trim($csvString);

    $parsedRows = str_getcsv($csv, "\r\n");
    $parsedCsv = [];
    foreach($parsedRows as $row) {
        if(trim($row) === "") {
            continue;
        }

        $parsedRow = str_getcsv($row);
        array_push($parsedCsv, $parsedRow);
    }
    return $parsedCsv;
}

function assertProcessCount($scriptToRun, $maxProcessInstancesAllowed, $exitCodeOnError = 99) {
    $psCommandOutput = shell_exec("ps auxwww");

    $psCommandOutputArray = explode(PHP_EOL, $psCommandOutput);

    $matchingProcessLines = [];
    foreach($psCommandOutputArray as $outputLine) {
        if(strpos($outputLine, $scriptToRun) !== false) {
            $matchingProcessLines[] = $outputLine;
        }
    }

    $matchingProcessLines = array_filter($matchingProcessLines, function($matchingProcessLine) {
        return strpos($matchingProcessLine, "/bin/sh -c") === false
            && strpos($matchingProcessLine, "sudo") === false;
    });

    if(count($matchingProcessLines) > $maxProcessInstancesAllowed) {
        echo PHP_EOL . implode(PHP_EOL, $matchingProcessLines) . PHP_EOL;
        echo PHP_EOL . sprintf("Process '" . $scriptToRun . "' is already running %d instances, exiting", $maxProcessInstancesAllowed) . PHP_EOL;
        exit($exitCodeOnError);
    }
}

/**
 * @param $arr
 * @return array
 */
function flattenArray($arr)
{
	$toFlatten = [$arr];
	$flattened = [];

	while(!empty($toFlatten)) {
		$element = array_shift($toFlatten);
		if(is_array($element)) {
			foreach($element as $nestedElement) {
				$toFlatten[] = $nestedElement;
			}
		} else {
			$flattened[] = $element;
		}
	}

	return $flattened;
}

function arrayOrderCsvData($colsOrdered, $arr, $allCols, $default = "") {
    $remainingCols = array_diff($allCols, $colsOrdered);

    $headers = array_merge($colsOrdered, $remainingCols);

    $resultData = [];
    foreach($arr as $line) {
        $output = [];

        foreach($headers as $col) {
            if(array_key_exists($col, $line)) {
                $output[] = $line[$col];
            } else {
                $output[] = $default;
            }
        }
        $resultData[] = $output;
    }

    return [
        "headers" => $headers,
        "data" => $resultData
    ];
}