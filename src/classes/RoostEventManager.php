<?php
/**
 * Created by PhpStorm.
 * User: work
 * Date: 9/11/18
 * Time: 15:21
 */

class RoostEventManager
{
	/** @var RoostEventListenerIface[] */
	private $listeners = [];
	private $verbose;

	public function __construct(bool $verbose = false) {
		$this->verbose = $verbose;
		if($this->verbose) {
			echo "Created " . static::class . PHP_EOL;
		}
	}

	public function registerListener($listener) {
		if($this->verbose) {
			echo "Registered listener: " . get_class($listener) . PHP_EOL;
		}
		$this->listeners[] = $listener;
	}

	public function event($event) {
		foreach($this->listeners as $listener) {
			if($listener->isInterestedInEvent($event)) {
				$listener->handleEvent($event);
			}
		}
	}

	public function listeningDone() {
		if($this->verbose) {
			echo "Listening done" . PHP_EOL;
		}
		foreach($this->listeners as $listener) {
			$listener->listeningDone();
		}
	}
}