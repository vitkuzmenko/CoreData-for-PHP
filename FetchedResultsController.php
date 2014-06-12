<?php

/*
 * Created 11/06/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * FetchedResultsController.php
 * FetchedResultsController
 */

namespace CoreData;

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class FetchedResultsController {
	
	private $persistentStore;
	
	private $fetchedRequest;
	
	private $fetchedObjects;
	
	public $error = array();
	
	function __construct ($persistentStore, $fetchedRequest) {
		$this->persistentStore = $persistentStore;
		$this->fetchedRequest = $fetchedRequest;
	}

	public function performFetch() {
		$query = $this->fetchedRequest->querySelectInString();
		
		$result = $this->persistentStore->executeQuery($query);
		
		if ($result == false) {
			array_push($this->error, $this->errorDescription($this->persistentStore, 400));
			return;
		}
		
		$fetchedObjects = array();
		
		while ($row = mysql_fetch_assoc($result)) {
			
			$class = $this->fetchedRequest->managedObjectClass();
			
			$object = new $class($this->persistentStore, $this->fetchedRequest->entity());
			$object->setDataFromArray($row);
						
			array_push($fetchedObjects, $object);
			
		}
		
		$this->fetchedObjects = $fetchedObjects;
		
		return $fetchedObjects;
	}
	
	public function fetchedObjects() {
		return $this->fetchedObjects;
	}
	
	public function firstObject() {
		if (!$this->fetchedObjects) {
			$this->fetchedObjects = $this->performFetch();
		}

		return $this->fetchedObjects[0];
	}

	private function errorDescription($store, $code) {
		
		$persistentStore = sprintf('Persistent Store %s', $store->name);
		
		switch ($code) {
			case 400:
				return sprintf('%s Query Error: %s.', $persistentStore, mysql_error($store->connection));
				break;
		}
		
	}

}
