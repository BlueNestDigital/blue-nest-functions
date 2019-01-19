<?php

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */
class InputFeedMysqlTable extends \MysqlTableDefinition
{
    function getTableName() {
        return "Input_Feed";
    }

    function getCols() {
        return \MysqlWriter::TableInstance([
            ["column", "type", "default", "null", "pk", "idx", "extra"],
            ["action", "varchar-30", NO_DEFAULT, null, false, false, []],
            ["object_id", "varchar-20", NO_DEFAULT, null, false, false, []],
            ["object_description", "varchar-1000", NO_DEFAULT, null, false, false, []],
            ["object", "text", NO_DEFAULT, null, false, true, []],
        ]);
    }

    function getMetaCols() {
        return \MysqlWriter::TableInstance([
            ["column", "type", "default", "null", "pk", "idx", "extra"],
            ["created_at", "timestamp", "CURRENT_TIMESTAMP", null, false, false, []],
            ["updated_by", "varchar-20", NO_DEFAULT, null, false, false, []],
            ["updated_at", "timestamp", NO_DEFAULT, null, false, false, []],
        ]);
    }

    function getCalculatedCols() {
        return [
            "updated_by" => [
                "level" => "table",
                "callback" => function () {
                    return getenv('USER');
                }
            ],
            "updated_at" => [
                "level" => "table",
                "callback" => function () {
                    return date("Y-m-d H:i:s");
                }
            ]
        ];
    }
}