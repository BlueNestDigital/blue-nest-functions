<?php

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */
class MysqlWriter
{
    /** @var MysqlTableDefinition */
    private $mysqlTableDefinition;
    private $testMode = false;
    private $shouldCommit = true;

    function __construct($tableName, MysqlTableDefinition $mysqlTableDefinition, $testMode = false) {
        $this->tableName = $tableName;
        $this->mysqlTableDefinition = $mysqlTableDefinition;
    }

    function shouldCommit($val) {
        $this->shouldCommit = $val;
    }

    static function createTableSqlGenerator($tableName, $colDefinitionData, $settings = []) {
        $createColumnSql = "";
        $primaryKeyColumns = [];
        $uniqueColumns = [];

        $autoIncrementColumn = array_key_exists('auto_increment', $settings) ? $settings['auto_increment']['col'] : null;

        foreach($colDefinitionData as $row) {
            if($createColumnSql !== "") {
                $createColumnSql .= "," . PHP_EOL;
            }
            $sql = "";
            $sql .= "`" . $row['column'] . "`";
            $sql .= " " . static::toMysqlDatatype($row['type']);


            if($autoIncrementColumn !== null && $autoIncrementColumn == $row['column']) {
                $sql .= ' AUTO_INCREMENT';
            }

            if($row['null'] !== null) {
                $sql .= " " . (($row['null']) ? "NULL" : "NOT NULL");
            }

            if($row['default'] !== BND_DEFAULT_VALUE) {
                $sql .= " DEFAULT " . $row['default'];
            }

            if(isset($row['unique']) && $row['unique'] === true) {
                $uniqueColumns[] = $row['column'];
            }
/*
            if($row['pk'] === true) {
                $primaryKeyColumns[] = $row['column'];
            }
*/
            if($row['pk'] === true) {
                $sql .= " PRIMARY KEY";
            }

            $createColumnSql .= $sql;
        }

        $createTableSql = "CREATE TABLE IF NOT EXISTS `" . $tableName . "` (" . PHP_EOL;
        $createTableSql .= $createColumnSql;
        /*
        if(!empty($primaryKeyColumns)) {
            $createTableSql .= ", " . PHP_EOL . "PRIMARY KEY (" . implode(", ", $primaryKeyColumns) . ")";
        }*/
        if(isset($settings["extra_create_sql"])) {
            $createTableSql .= ", " . PHP_EOL . $settings["extra_create_sql"] . PHP_EOL;
        }
        $createTableSql .= PHP_EOL . ");";

        return $createTableSql;
    }

    static function mysqlGetColBindType($mysqlType) {
        $dataType = $mysqlType;

        $paramType = null;
        if(strpos($dataType, "varchar") !== false) {
            $paramType = "s";
        } else if(strpos($dataType, "text") !== false) {
            $paramType = "s";
        } else if(strpos($dataType, "int") !== false) {
            $paramType = "i";
        } else if(strpos($dataType, "float") !== false) {
            $paramType = "d";
        } else if(strpos($dataType, "decimal") !== false) {
            $paramType = "d";
        } else if(strpos($dataType, "boolean") !== false) {
            $paramType = "i";
        } else if(strpos($dataType, "date") !== false) {
            $paramType = "s";
        } else if(strpos($dataType, "datetime") !== false) {
            $paramType = "s";
        } else if(strpos($dataType, "timestamp") !== false) {
            $paramType = "s";
        } else if(strpos($dataType, "currency") !== false) {
            $paramType = "d";
        } else {
            throw new \LogicException("Unhandled datatype: " . $dataType);
        }

        return $paramType;
    }

    function valuesQueryPlaceholder($includedColumns) {
        $placeHolders = array_fill(0, count($includedColumns), "?");
        return implode(", ", $placeHolders);
    }

    function valuesQuery($mysqli, $values, $bindTypesAsString) {
        $bindParamTypes = str_split($bindTypesAsString);
        foreach($values as $i=>&$value ) {
            $bindParamType = $bindParamTypes[$i];
            if($value === null) {
                $value = "null";
            } else if($bindParamType === "s") {
                $value = '"' . mysqli_real_escape_string($mysqli, $value) . '"';
            } else if($bindParamType === "i" || $bindParamType === "d") {
                if(empty($value)) {
                    $value = "null";
                } else if(!is_numeric($value)) {
                    throw new \RuntimeException("Bad numeric column value: " . debugVar($value) . " from " . print_r($values, true));
                }
            } else {
                throw new \LogicException("Unhandled bind type: " . $bindParamType);
            }
        }

        return implode(", ", $values);
    }

    function writeToMysqlHelper(MysqlConnection $mysqlConn, $tableName, $data, $skipColumns, $skipInclude = -1) {
        $batchSize = 100000;
        $useRawStringQuery = true;

        $mysqli = $mysqlConn->getConnection();

        $colDefinitions = $this->mysqlTableDefinition->getColDefs();
        $calculatedColDefs = $this->mysqlTableDefinition->getCalculatedCols();

        $tableHeaders = [];
        foreach($colDefinitions as $colDef) {
            $tableHeaders[] = $colDef['column'];
        }

        $includedColumns = array_filter($tableHeaders, function($val) use($skipColumns, $skipInclude) {
            if($skipInclude === -1) {
                return !in_array($val, $skipColumns);
            } else {
                return in_array($val, $skipColumns);
            }
        });

        $includedColumnsQuoted = encloseArrayValues($includedColumns, "`");

        if(!$useRawStringQuery) {
            $rowInsertQuery = "INSERT into `" . $tableName . "` (" . implode(", ", $includedColumnsQuoted) . ") VALUES (" . $this->valuesQueryPlaceholder($includedColumns) . ")";
        } else {
            $rowInsertQuery = "INSERT into `" . $tableName . "` (" . implode(", ", $includedColumnsQuoted) . ") VALUES ";
        }

        $doUpsert = true;
        if($doUpsert) {
            $onDuplicateKeySqlStatements = [];

            foreach($includedColumns as $includedColumn) {
                $enclosedCol = enclose($includedColumn, '`');
                $onDuplicateKeySqlStatements[] = $enclosedCol . "=" . "VALUES(" . $enclosedCol . ")";
            }

            $rowInsertQuery .= " ON DUPLICATE KEY UPDATE " . implode(", ", $onDuplicateKeySqlStatements);
        }

        $calculatedTableColumnValues = [];
        $calculatedColDefsPerRowCallbacks = [];
        foreach($calculatedColDefs as $col => $calculatedColDef) {
            $calculatedColDef = $calculatedColDefs[$col];

            if($calculatedColDef['level'] === "table") {
                $calculatedCallback = $calculatedColDef['callback'];
                $calculatedTableColumnValues[$col] = $calculatedCallback();
            } else if($calculatedColDef['level'] === "row") {
                $calculatedColDefsPerRowCallbacks[$col] = $calculatedColDef;
            }
        }

        $colDefinitionsByKey = [];
        foreach($colDefinitions as $colDefinition) {
            $colDefinitionsByKey[$colDefinition['column']] = $colDefinition;
        }

        if(!$useRawStringQuery) {
            if((!$stmt = $mysqli->prepare($rowInsertQuery))) {
                die("Could not prepare query: " . $mysqli->error);
            }
            $stmtReflection = new ReflectionClass('mysqli_stmt');
            $bindParamMethod = $stmtReflection->getMethod("bind_param");
        } else {
            $stmt = null;
            $stmtReflection = null;
            $bindParamMethod = null;
        }


        $colDataTypes = [];
        $paramTypes = [];
        foreach($includedColumns as $currentCol) {
            $columnData = $colDefinitionsByKey[$currentCol];

            $colDataTypes[$currentCol] = $this->toMysqlDatatype($columnData["type"]);
            $paramTypes[$currentCol] = $this->mysqlGetColBindType($colDataTypes[$currentCol]);
        }

        $insertCount = 0;
        /** @var CsvRow $row */
        $progress = new ProgressPrinter(count($data), 300000, "Create Insert statements");
        $rowInsertStatements = [];
        foreach($data as $i=>$row) {
            $progress->increment();
            if($row instanceof CsvRow) {
                /** @var \CsvRow $row */
                $row = $row->toArray();
            }
            if($row === null) {
                continue;
            }
            //if($stmt = $mysqli->prepare($rowInsertQuery)) {
                $bindParamTypes = '';
                $valArray = [];
                foreach($includedColumns as $currentCol) {
                    $columnData = $colDefinitionsByKey[$currentCol];

                    $dataType = $colDataTypes[$currentCol];
                    $paramType = $paramTypes[$currentCol];

                    if(array_key_exists($currentCol, $calculatedColDefsPerRowCallbacks)) {
                        $value = $calculatedColDefsPerRowCallbacks[$currentCol]['callback']($row);
                    } else if(array_key_exists($currentCol, $calculatedTableColumnValues)) {
                        $value = $calculatedTableColumnValues[$currentCol];
                    } else if(!array_key_exists($currentCol, $row)) {
                        $value = null;
                    } else {
                        $value = &$row[$currentCol];

                        if($value === null) {
                            if(!$columnData['null']) {
                                throw new Exception("writeToMysqlHelper: Null value encountered for non-nullable column '" . $currentCol . "': " . print_r($row, true));
                            }
                        } else if($dataType === "date" || $dataType === "datetime") {
                            if($value !== "" && $value !== null) {
                                if($dataType === "date") {
                                    $value = DateTime::createFromFormat('m/d/Y', $value);
                                } else {
                                    $value = DateTime::createFromFormat('m/d/Y G:i', $value);
                                }
                            } else {
                                $value = false;
                            }

                            if($value == false) {
                                if(!$columnData['null']) {
                                    throw new Exception("Could not convert " . $dataType . ": " . $currentCol . ": " . $value);
                                } else {
                                    $value = null;
                                }
                            } else {
                                if($dataType === "date") {
                                    $value = $value->format('Y-m-d');
                                } else {
                                    $value = $value->format('Y-m-d H:i:s');
                                }
                            }
                        } else if($dataType === "boolean") {
                            $value = $value ? 1 : 0;
                        }
                    }

                    $valArray[] = $value;
                    $bindParamTypes .= $paramType;
                }

                if(!$useRawStringQuery) {
                    $argArrayForReflection = [&$bindParamTypes];
                    foreach($valArray as &$theVal) {
                        $argArrayForReflection[] = &$theVal;
                    }
                }

                if(!$this->testMode) {
                    if($useRawStringQuery) {
                        $rowInsertStatements[] = "(" . $this->valuesQuery($mysqli, $valArray, $bindParamTypes) . ")";
                    } else {
                        try {
                            $bindParamMethod->invokeArgs($stmt, $argArrayForReflection);
                        } catch(Exception $e) {
                            echo "Query data: " . print_r([
                                    "query" => $rowInsertQuery,
                                    "params" => $bindParamTypes,
                                    "values" => $valArray],
                                    true) . PHP_EOL;
                            throw $e;
                        }
                        $insertCount += 1;
                    }
                    //$stmt->bind_result($district);
                    //$stmt->fetch();
                } else {
                    echo "Running in test mode, query data: " . print_r([$rowInsertQuery, $paramTypes, $valArray], true) . PHP_EOL;
                }

                try {
                    //$stmt->execute();
                } catch(Exception $e) {
                    echo "Query data: " . print_r([
                            "query" => $rowInsertQuery,
                            "params" => $paramTypes,
                            "values" => $valArray],
                            true) . PHP_EOL;
                    throw $e;
                }

            //} else {
            //    die("Could not prepare query: " . $mysqli->error);
            //}
                if(count($rowInsertStatements) % $batchSize === 0 || ($i + 1) === count($data)) {
                    echo "Inserting batch of size " . count($rowInsertStatements) . PHP_EOL;
                    $fullQuery = $rowInsertQuery . implode(", ", $rowInsertStatements);

                    file_put_contents("last_query.log", $fullQuery);
                    $res = $mysqli->query($fullQuery);
                    echo "Result size: " . count($res) . PHP_EOL;

                    if($res === false) {
                        echo bndAbbreviate($fullQuery, 500) . PHP_EOL . PHP_EOL;
                        throw new \Exception("Query failed: " . $mysqli->error);
                    }
                    $rowInsertStatements = [];
                }
        }

        echo "Done creating insert rows, committing" . PHP_EOL;

        //$stmt->close();
        /*
        file_put_contents("last_query.log", $fullQuery);

        $res = $mysqli->query($rowInsertQuery);
        var_dump($res);

        if($res === false) {
            print_r($rowInsertQuery);
            echo PHP_EOL . PHP_EOL;
            throw new \Exception("Query failed: " . $mysqli->error);
        }
*/
        if($this->shouldCommit && !$mysqli->commit()) {
            throw new \Exception("Could not commit to mysql: "  . $mysqli->error);
            echo "Done committing" . PHP_EOL;
        } else {
            echo "Done, skipping commit (shouldCommit==false)" . PHP_EOL;
        }

        return [
            "success" => true,
            "inserted-records-count" => $insertCount
        ];
    }

    static function toMysqlDatatype($typeDef) {
        $nativeTypes = [
            "tinyint", "smallint", "mediumint", "int", "bigint", "text", "boolean", "date", "datetime", "timestamp"
        ];

        $typeDef = strtolower($typeDef);

        if(in_array($typeDef, $nativeTypes)) {
            $actual = $typeDef;
        } else if(strpos($typeDef, "varchar") === 0) {
            $actual = str_replace("-", "(", $typeDef) . ")";
        } else if(strpos($typeDef, "char") === 0) {
            $actual = str_replace("-", "(", $typeDef) . ")";
        } else if($typeDef === "date") {
            $actual = "date";
        } else if($typeDef === "url") {
            $actual = "text";
        } else if(strpos($typeDef, "int") === 0) {
            $actual = str_replace("-", "(", $typeDef) . ")";
        } else if($typeDef === "bool") {
            $actual = "boolean";
        } else if($typeDef === "currency") {
            $actual = "decimal(10, 2)";
        } else if($typeDef === "float") {
            $actual = "float";
        }else {
            throw new \LogicException("Unhandled column datatype: " . $typeDef);
        }

        return $actual;
    }

    static function TableInstance($data) {
        $colHeaders = array_shift($data);

        foreach($data as &$row) {
            $keyedRow = [];
            foreach($colHeaders as $i => $colHeader) {
                $keyedRow[$colHeader] = $row[$i];
            }
            $row = $keyedRow;
        }

        return $data;
    }

    function isColumnDefNumericColumnType($colDef) {
        if(strpos($colDef, "int") === 0) {
            return true;
        }

        return false;
    }
}