<?php

use Roost\Collections\IndexedMap;

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2018 Blue Nest Digital LLC
 */

class MongoDbWriter
{
    public static function writeMultipleTables(MongoDB\Driver\Manager $mongoDb, array &$data, string $mode = "INSERT") {
        $mode = strtoupper($mode);

        foreach($data as $collectionToInsertInto => &$rowsToInsert) {
            static::writeToTable($mongoDb, $collectionToInsertInto, $rowsToInsert, $mode);
        }
    }

    public static function writeToTable(MongoDB\Driver\Manager $mongoDb, string $collectionToInsertInto, array &$rowsToInsert, string $mode = "INSERT") {

    	$indexedMap = new IndexedMap();

        $mongoBulkWrite = new \MongoDB\Driver\BulkWrite();
            $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 100);

            $index = 0;
            foreach($rowsToInsert as &$rowToInsert) {

                if(isset($rowToInsert["local-index"])) {
                	$indexedMap->addKey($rowToInsert["local-index"]);
				}

                if($mode === "INSERT") {

                    /** @var MongoDB\BSON\ObjectId $insertedId */
                    $insertedId = $mongoBulkWrite->insert($rowToInsert);

                    if(array_key_exists("_id", $rowToInsert)) {
                        $insertedId = $rowToInsert["_id"];
                    } else {
                        $insertedId = (string) $insertedId;
                    }

                    $rowToInsert["_id"] = $insertedId;

                } else if($mode === "UPDATE" || $mode === "UPSERT") {
                    if(isset($rowToInsert["query"])) {
                        $filter = $rowToInsert["query"];
                    } else {
                        $filter = ['_id' => $rowToInsert["_id"]];
                    }

                    $mongoBulkWrite->update(
                    $filter,
                    [
                        '$set' => $rowToInsert
                    ],
                    [
                        'multi' => false,
                        'upsert' => $mode === "UPSERT"
                ]);
                } else {
                    throw new \InvalidArgumentException("Unrecognized mode: " . $mode);
                }

                $rowToInsert["index"] = $index++;
            }

            /** @var MongoDB\BulkWriteResult $bulkWriteResult */
            $bulkWriteResult = $mongoDb->executeBulkWrite($collectionToInsertInto, $mongoBulkWrite, $writeConcern);

            $resultLog = [
                "inserted" => $bulkWriteResult->getInsertedCount(),
                "upserted" => $bulkWriteResult->getUpsertedCount(),
                "matched" => $bulkWriteResult->getMatchedCount(),
                "modified" => $bulkWriteResult->getModifiedCount(),
                "deleted" => $bulkWriteResult->getDeletedCount()
            ];

            $insertedIds = $bulkWriteResult->getInsertedIds();

            foreach($insertedIds as $insertedId) {
            	$indexedMap->addValue($insertedId);
			}

/*
            foreach($rowsToInsert as $rowToInsert) {
            	$index = $rowToInsert["index"];
            	$rowsToInsert["id"] = $insertedIds[$index];
			}
*/
            foreach($resultLog as $operation => $count) {
                if($count === 0) {
                    unset($resultLog[$operation]);
                }
            }
            echo "Result for writing to '" . $collectionToInsertInto . "':" . PHP_EOL;
            print_r($resultLog);

            return $indexedMap;
    }
}