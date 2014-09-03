<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * ManagedObject.php
 * ManagedObject
 */

namespace CoreData;

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class ManagedObject {
	
	public $persistentStore;
	
	protected $entity;
	
	public $data = array();
	
	public $error = array();
	
	function __construct(EntityDescription $entity = null, PersistentStore $store = null) {
		if (!$store) {
			$store = \CoreData::getStore();
		}
		
		$this->persistentStore = $store;
		
		$this->entity = $entity;
	}
	
	public function entity() {
		if ($this->entity) {
			return $this->entity;
		}
		
		$entity = new EntityDescription(null);
		
		$this->entity = $entity;
		
		return $entity;
	}
	
	public function setEntity(EntityDescription $entity) {
		$this->entity = $entity;
	}
	
	public function setDataFromArray(array $array = array()) {
		
		foreach ($array as $key => &$row) {
			$row = stripcslashes($row);
		}
		
		$this->data = $array;
	}
	
	public function setDataFromPostRequest(&$post, &$error) {
		
		foreach ($post as $key => $value) {
			
			if ($key == $this->entity()->identifierFieldName) {
				$value = intval($value);
				if ($value == 0) {
					continue;
				}
			}
			
			$this->$key = $value;
		}
	}
	
	public function setValueForKey($key, $value) {
		if (is_string($value)) {
			$value = stripcslashes($value);
		}
	
		$this->data[$key] = $value;
	}
	
	public function __set($key, $value) {
	
		$methodName = sprintf('set' . ucfirst($key));
		
		if (method_exists($this, $methodName)) {
			$this->$methodName($value);
		} else {
			$this->setValueForKey($key, $value);
		}
	}
	
	public function getValueForKey($key) {
		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		} else {
			return null;
		}
	}
	
	public function getAllData() {
		$data = array();
		
		foreach ($this->data as $key => $value) {
			$data[$key] = $this->$key;
		}
		
		return $data;
	}
	
	public function __get($key) {
	
		$methodName = sprintf('get' . ucfirst($key));
		$underscore = mb_substr($key, 0, 1, "UTF-8") == "_";
		
		if ($underscore) {
			return $this->getValueForKey(ltrim($key, "_"));
		} else if (method_exists($this, $methodName)) {
			return $this->$methodName();
		} else {
			if (array_key_exists($key, $this->data)) {
				return $this->getValueForKey($key);
			} else {
				return null;
			}
		}
	}
	
	public static function init($id, EntityDescription $entity) {
		$idField = $entity->identifierFieldName();
		
		$object = new self;
		$object->setEntity($entity);
		$object->$idField = $id;
		$object->actual();
		return $object;
	}
	
	/**
	 * Actual data in object.
	 * 
	 * @access public
	 * @param bool $forFilledFields (default: false) Collect WHERE section from all filled parameters in data
	 * @param mixed $excludeField (default: null)
	 * @return void
	 */
	public function actual($forFilledFields = false, $excludeField = null) {
		
		$idField = $this->entity()->identifierFieldName();
		
		if (!$forFilledFields && !$this->$idField) {
			return $this;
		}
		
		if ($forFilledFields && !$this->data) {
			return $this;
		}
		
		$predicate = new Predicate();
		
		if ($forFilledFields) {
			$predicate->addEqualOperandFromArray($this->data, $excludeField);
		} else {
			$predicate->addEqualOperand($idField, $this->$idField);
		}
		
		$fetchedRequest = new FetchedRequest($this->entity(), $predicate);
		
		$fetchedResultsController = new FetchedResultsController($fetchedRequest, false, $this->persistentStore);
		$fetchedObjects = $fetchedResultsController->performFetch();
		
		$object = $fetchedResultsController->firstObject();
	
		if ($object) {
			$this->data = $object->data;
		}
		
		return $this;
	}
	
	
	/**
	 * Get all parametrs as string.
	 * 
	 * @access private
	 * @return void
	 */
	private function getStringParametersFromData() {
		$predicate = new Predicate();
		$predicate->addEqualOperandFromArray($this->data);
		
		return $predicate->predicateInString(',', 'SET');
	}
	
	// !Manage Object in MySQL
	
	public function save() {
		
		if (!$this->entity()) {
			array_push($this->error, $this->errorDescription(400));
		}
		
		$insert = !array_key_exists($this->entity()->identifierFieldName(), $this->data);
		
		if ($insert) {
			$this->insert();
		} else {
			$this->update();
		}
		
		$this->saveComplete();
	}
	
	public function saveComplete() {
		
	}
	
	protected function insert() {
		$table = $this->entity()->tableInString();
		$parameters = $this->getStringParametersFromData();
		
		$query = sprintf("INSERT INTO %s %s", $table, $parameters);
		
		$persistentStore = $this->persistentStore;
		$bool = $persistentStore->executeQuery($query);
		
		if (!$bool) {
			array_push($this->error, mysql_error($persistentStore->connection));
		}
		
		$identifierFieldName = $this->entity()->identifierFieldName();
		
		$this->$identifierFieldName = $persistentStore->insertId();
	}
	
	protected function update() {
		$identifierFieldName = $this->entity()->identifierFieldName();
		
		$predicate = new Predicate($identifierFieldName, $this->$identifierFieldName);
	
		$table = $this->entity()->tableInString();
		$parameters = $this->getStringParametersFromData();
		$predicateString = $predicate->predicateInString();
	
		$query = sprintf("UPDATE %s %s %s LIMIT 1", $table, $parameters, $predicateString);
		
		$bool = $this->persistentStore->executeQuery($query);
		
		if (!$bool) {
			array_push($this->error, mysql_error($this->persistentStore->connection));
		}
	}

	public function delete() {
		$identifierFieldName = $this->entity()->identifierFieldName();
		
		$predicate = new Predicate($identifierFieldName, $this->$identifierFieldName);
	
		$table = $this->entity()->tableInString();
		$parameters = $this->getStringParametersFromData();
		$predicateString = $predicate->predicateInString();
	
		$query = sprintf("DELETE FROM %s %s LIMIT 1", $table, $predicateString);
		
		$bool = $this->persistentStore->executeQuery($query);
		
		if (!$bool) {
			array_push($this->error, mysql_error($this->persistentStore->connection));
		}
	}
	
	// Equals
	
	public function isEqualById($object) {
		$idField = $this->entity()->identifierFieldName();
		if (get_class($this) == get_class($object)) {
			return $this->$idField == $object->$idField;
		} else {
			return false;
		}
	}

	/**
	 * errorDescription function.
	 * - Error Description
	 * @access private
	 * @param mixed &$store
	 * @param mixed $code
	 * @return string
	 */
	private function errorDescription($code) {
		
		switch ($code) {
			case 400:
				return sprintf('Entity Error: Entity can not be null.');
				break;
		}
	}
}
