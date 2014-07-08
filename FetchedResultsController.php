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
	
	private $allObjectsCount;
	
	public $counting = false;
	
	public $error = array();
	
	function __construct ($fetchedRequest, $counting = false, PersistentStore $store = null) {
		
		if (!$store) {
			$store = \CoreData::getStore();
		}
		
		$this->persistentStore = $store;
		$this->fetchedRequest = $fetchedRequest;
		$this->counting = $counting;
	}
	
	function __destruct() {
		if (count($this->error)) {
			print_r($this->error);
		}
	}

	public function executeCounting() {
		$query = $this->fetchedRequest->querySelectCountInString();
		
		$result = $this->persistentStore->executeQuery($query);
		
		$row = mysql_fetch_row($result);
		
		$this->allObjectsCount = $row[0];

		return $this->allObjectsCount;
	}

	public function performFetch() {
	
		if ($this->counting) {
			$this->executeCounting();
		}
		
		$query = $this->fetchedRequest->querySelectInString();
		
		$result = $this->persistentStore->executeQuery($query);
		
		if ($result == false) {
			array_push($this->error, $this->errorDescription($this->persistentStore, 400) . 'Query: ' . $query);
			return;
		}
		
		$fetchedObjects = array();
		
		while ($row = mysql_fetch_assoc($result)) {
			
			$class = $this->fetchedRequest->managedObjectClass();
			
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
			return null;
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

	private function errorDescription($store, $code) {
		
		$persistentStore = sprintf('Persistent Store %s', $store->name);
		
		switch ($code) {
			case 400:
				return sprintf('%s Query Error: %s.', $persistentStore, mysql_error($store->connection));
				break;
		}
		
	}

}
