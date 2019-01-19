<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

use BlueNest\BlueBing\AbstractBlueBingClass;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ActionLog {
    /** @var \Monolog\Logger  */
    static $logInstance = null;

    /**
     * @return \Monolog\Logger
     * @throws Exception
     */
    static function getLogger() {
        if(static::$logInstance === null) {
            $logInstance = new \Monolog\Logger('application');

            //$client = new \MongoDB\Client('mongodb://localhost:27017');
            //$mongodb = new \Monolog\Handler\MongoDBHandler($client, 'logs', 'prod');
            //$logInstance->pushHandler($mongodb);

            $logInstance->pushHandler(new StreamHandler('logs/action-log.log', Logger::INFO));

            if($logInstance === null) {
                throw new \Exception("Could not instantiate logger");
            }

            static::$logInstance = $logInstance;
        }

        return static::$logInstance;
    }

    static function log($actionName, $objectId, $objectDescription, $object) {
        return;
        $logger = static::getLogger();

        $objectType = getTypeOrClass($object);

        if($object instanceof AbstractBlueBingClass) {
            if($object->apiInstance() !== null) {
                $object = get_object_vars($object->apiInstance());

                foreach($object as $key=>$val) {
                    if(empty($val)) {
                        unset($object[$key]);
                    }
                }
            }
        }

        $msgObject = [
            "action" => $actionName,
            "objectId" => $objectId,
            "objectType" => $objectType,
            "objectDescription" => $objectDescription,
            "object" => $object
        ];
        $logger->info(json_encode($msgObject, JSON_UNESCAPED_SLASHES));
    }
}