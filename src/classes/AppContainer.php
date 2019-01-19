<?php

use Doctrine\Common\EventManager;

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */
class AppContainer {
    private $appRegistry = []; // keyed by class
    private $appRegistryByKey = [];

    /** @var AppContainer */
    private static $appContainerInstance = null;

    function __construct() {
        AppContainer::$appContainerInstance = $this;
    }

    function event($event) {
    	/** @var RoostEventManager $eventManager */
    	$eventManager = $this::getByKey("event-manager");
    	$eventManager->event($event);
	}

	function listeningDone() {
    	/** @var RoostEventManager $eventManager */
    	$eventManager = $this::getByKey("event-manager");
    	$eventManager->listeningDone();
	}

    public static function instance() {
        return AppContainer::$appContainerInstance;
    }

    function register($key, $object = null) {
        if($object === null) {
            $object = $key;
            $key = "default";
        }
        $class = get_class($object);

        if(!array_key_exists($class, $this->appRegistry)) {
            $this->appRegistry[$class] = [];
        }

        $classRegistry = &$this->appRegistry[$class];

        if(empty($classRegistry)) {
            $classRegistry['default'] = $object;
        }

        $classRegistry[$key] = $object;

        /**
         * If no key is provided, use a placeholder name
         */
        if($key !== "default") {
            $this->appRegistryByKey[$key] = $object;
        }
    }

    public static function get($class, $key = null) {
        return AppContainer::instance()->getFromInstance($class, $key);
    }

	/**
	 * @param $class
	 * @param null $key
	 * @return mixed
	 * @throws Exception
	 */
    function getFromInstance($class, $key = null) {
        $classRegistry = $this->appRegistry[$class];

        if($key === null) {
            if(!array_key_exists("default", $classRegistry)) {
                throw new \Exception("Missing default key in class registry " . $class);
            }
            return $classRegistry['default'];
        }

        if(!array_key_exists($key, $classRegistry)) {
            throw new \Exception("Unknown key " . $key . " in class registry " . $class);
        }

        return $classRegistry[$key];
    }

    public static function getByKey($key) {
    	return AppContainer::instance()->appRegistryByKey[$key];
	}

    function __get($name) {
        if(array_key_exists($name, $this->appRegistryByKey)) {
            return $this->appRegistryByKey[$name];
        } else {
            throw new \InvalidArgumentException("No component registered for key: " . $name);
        }
    }
}