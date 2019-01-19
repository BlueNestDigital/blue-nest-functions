<?php

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */
abstract class MysqlTableDefinition {
    abstract function getTableName();
    abstract function getCols();
    abstract function getMetaCols();
    abstract function getCalculatedCols();

    function getColDefs() {
        $colDefs = array_merge($this->getCols(), $this->getMetaCols());
        return $colDefs;
    }

    function getTableSettings() {
        return [];
    }
}