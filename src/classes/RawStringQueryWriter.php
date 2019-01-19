<?php

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */
class RawStringQueryWriter
{
    private $bindParamMethod = null;
    private $rowInsertQuery;
    private $stmt;
    private $bindParamTypes = [];
    private $valArray;

    function prepare($rowInsertQuery) {
        $this->rowInsertQuery = "INSERT into `" . $tableName . "` (" . implode(", ", $includedColumnsQuoted) . ") VALUES ";
    }

    function add($value, $paramType) {
        $this->valArray[] = $value;
        $this->bindParamTypes .= $paramType;
    }

    function bind() {
        $rowInsertStatements[] = "(" . $this->valuesQuery($mysqli, $this->valArray, $this->bindParamTypes) . ")";
    }

    function run() {
        $fullQuery = $rowInsertQuery . implode(", ", $rowInsertStatements);

        file_put_contents("last_query.log", $fullQuery);
        $res = $mysqli->query($fullQuery);
    }
}