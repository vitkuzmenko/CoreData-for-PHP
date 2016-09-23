<?php

/*
 * Created 11/06/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CDFetchedResultsController.php
 * CDFetchedResultsController
 */

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class CDFetchedResultsController {
	
	private $persistentStore;
	
	private $fetchedRequest;
	
	private $fetchedObjects;
	
	private $allObjectsCount;
	
	public $counting = false;
	
	public $store;
	
	function __construct ($fetchedRequest, $counting = false, CDPersistentStore $store = null) {
		
		if (!$store) {
			$store = CoreData::getStore();
		}
		
		$this->persistentStore = $store;
		$this->fetchedRequest = $fetchedRequest;
		$this->counting = $counting;
		$this->store = $store;
	}
	
	function __destruct() {

	}

	public function executeCounting() {
		$query = $this->fetchedRequest->querySelectCountInString();

		$result = $this->persistentStore->executeQuery($query);
		
		$connection = $this->store->connection;
		CDError::setCode($connection->errno, $connection->error);
		
		if (!CDError::checkForError()) {
			return;
		}
		
		$row = mysqli_fetch_row($result);
		
		$this->allObjectsCount = $row[0];

		return $this->allObjectsCount;
	}

	public function performFetch() {
	
		if ($this->counting) {
			$this->executeCounting();
		}
		
		$connection = $this->store->connection;
		
		$query = $this->fetchedRequest->querySelectInString();
		
		$result = $this->persistentStore->executeQuery($query);
		
		CDError::setCode($connection->errno, $connection->error);
		
		if (!CDError::checkForError()) {
			return;
		}
		
		$fetchedObjects = array();
		
		while ($row = mysqli_fetch_assoc($result)) {
			
			$class = $this->fetchedRequest->managedObjectClass();
			
			if (CoreData::$isUnderscore) {
				$class = CDHelper::underscoreToCamelCase($class, true);
			}
			
			$object = new $class($this->fetchedRequest->entity(), $this->persistentStore);
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

		if (!$this->fetchedObjects) {
			return;
		} 
		
		return $this->fetchedObjects[0];
	}
	
	public function getValueSetForField($field) {
		
		if (!$this->fetchedObjects) {
			$this->performFetch();
		}
		
		$objects = $this->fetchedObjects;
		
		$array = array();
		
		foreach ($objects as $key => $value) {
			array_push($array, $value->getValueForKey($field));
		}
		
		return $array;
	}

}
