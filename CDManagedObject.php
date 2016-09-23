<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CDManagedObject.php
 * CDManagedObject
 */

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class CDManagedObject {
	
	public $persistentStore;
	
	public $data = array();
	
	public $entity;
	
	// !Private properties
	
	private $inStore = false;
	
	/**
	 * Fields in Table
	 * 
	 * (default value: array())
	 * 
	 * @var array
	 * @access private
	 */
	private $fields = array();
	
	function __construct(CDEntityDescription $entity = null, CDPersistentStore $store = null) {
		if (!$store) {
			$store = CoreData::getStore();
		}
		
		$this->persistentStore = $store;
		
		$this->entity = $entity;
	}
	
	// Entity
	
	public function entity() {
		if ($this->entity) {
			return $this->entity;
		}
				
		if (get_class($this) == 'CDManagedObject') {
			$entity = new CDEntityDescription(null);
		} else {
			$entity = $this->getBaseEntityDescription();
		}
		
		$this->entity = $entity;
		
		return $entity;
	}
	
	public function setEntity(CDEntityDescription $entity) {
		$this->entity = $entity;
	}
	
	public function getBaseEntityDescription() {
		
		$class = get_class($this);
		$tableName = $class;
		
		if (CoreData::$isUnderscore) {
			$tableName = CDHelper::camelCaseToUnderscore($class);
		}
		
		return new CDEntityDescription($tableName, get_class($this), $this->fields);
	}
	
	// !Data
	
	public function setDataFromArray(array $array = array()) {
		
		$data = array();
		
		foreach ($array as $key => $row) {
			if (CoreData::$isUnderscore) {
				$key = CDHelper::underscoreToCamelCase($key);
			}
			$data[$key] = stripcslashes($row);
		}
		
		$this->data = $data;
	}
	
	public function setDataFromPostRequest(&$post) {
		
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
	
	public function __set($key, $value) {
	
		$methodName = sprintf('set' . ucfirst($key));
		$underscore = mb_substr($key, 0, 1, "UTF-8") == "_";
		
		if ($underscore) {
			return $this->setValueForKey(ltrim($key, "_"), $value);
		} else if (method_exists($this, $methodName)) {
			$this->$methodName($value);
		} else {
			$this->setValueForKey($key, $value);
		}
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
				return;
			}
		}
	}
	
	public function setValueForKey($key, $value) {
		if (is_string($value)) {
			$value = stripcslashes($value);
		}

		$this->data[$key] = $value;
	}
	
	public function getValueForKey($key) {
		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		} else {
			return;
		}
	}
	
	/**
	 * Get all data for gelation include.
	 * 
	 * @access public
	 * @param mixed $excludeFileds (default: null)
	 * @return void
	 */
	public function getRelationData($excludeFileds = null) {
		return $this->getData($excludeFileds);
	}
	
	/**
	 * Get all data from objects in array.
	 * 
	 * @access public
	 * @param mixed $excludeFileds (default: null)
	 * @return void
	 */
	public function getData($excludeFileds = null) {
		$data = array();
		
		if (is_null($excludeFileds)) {
			$excludeFileds = array();
		}
		
		$isArray = is_array($excludeFileds);
		$isString = is_string($excludeFileds);
		
		foreach ($this->data as $key => $value) {
			if ($isArray && in_array($key, $excludeFileds) 
			|| ($isString && $key == $excludeFileds)) {
				continue;				
			}
			
			$dataKey = $key;
			
			if (CoreData::$outputIsUnderscore) {
				$dataKey = CDHelper::camelCaseToUnderscore($key);
			}
		
			$value = $this->$key;
			
			if (is_numeric($value)) {
				$value = intval($value);
			} else if (is_string($value) && empty($value)) {
				$value = null;
			}

			if (is_object($value) && is_subclass_of($value, 'CDManagedObject')) {
				$value = $value->getRelationData();
				$id = $value[$this->entity()->identifierFieldName()];
				if (is_null($id) || $id == 0) {
					$value = null;
				}
			}
			
			$data[$dataKey] = $value;
		}
		
		return $data;
	}
	
	public static function init($id, CDEntityDescription $entity = null) {
		$idField = CDEntityDescription::identifierFieldName();

		$class = get_called_class();	
		$object = new $class();
		if ($entity) {
			$object->setEntity($entity);
			$idField = $entity->identifierFieldName();
		}
		$object->$idField = $id;
		if ($object->actual() || CDError::checkForError()) {
			return $object;
		}
		
		return;
	}
	
	/**
	 * Actual data in object.
	 * 
	 * @access public
	 * @param bool $forFilledFields (default: false) Collect WHERE section from all filled parameters in data
	 * @param mixed $excludeField (default: null)
	 * @return void
	 */
	public function actual($forFilledFields = false, $excludeFilledField = null) {
		
		$idField = $this->entity()->identifierFieldName();
		
		if (!$forFilledFields && !$this->$idField) {
			return $this;
		}
		
		if ($forFilledFields && !$this->data) {
			return $this;
		}
		
		$predicate = new CDPredicate();
		
		if ($forFilledFields) {
			$predicate->addEqualOperandFromArray($this->data, $excludeFilledField);
		} else {
			$predicate->addEqualOperand($idField, $this->$idField);
		}
		
		$fetchedRequest = new CDFetchedRequest($this->entity(), $predicate);
		$fetchedResultsController = new CDFetchedResultsController($fetchedRequest, false, $this->persistentStore);
		$object = $fetchedResultsController->firstObject();
	
		if ($object) {
			$this->inStore = true;
			$this->data = $object->data;
		} else {
			$this->inStore = false;
		}
		
		if (!CDError::checkForError()) {
			return false;
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
		$predicate = new CDPredicate();
		$predicate->addEqualOperandFromArray($this->data);
		
		return $predicate->predicateInString(',', 'SET');
	}
	
	// !Manage Object in MySQL
	
	public function save() {
		
		if (!$this->entity()) {
			CDError::setError('Entity Error: Entity can not be null.');
		}
		
		$insert = !array_key_exists($this->entity()->identifierFieldName(), $this->data);
		
		if ($insert) {
			return $this->insert();
		} else {
			return $this->update();
		}
	}
	
	public function insert() {
		$table = $this->entity()->tableInString();
		$parameters = $this->getStringParametersFromData();
		
		$query = sprintf("INSERT INTO %s %s", $table, $parameters);
		
		$persistentStore = $this->persistentStore;
		$bool = $persistentStore->executeQuery($query);
		
		$connection = $persistentStore->connection;
		CDError::setCode($connection->errno, $connection->error);
		
		if (!CDError::checkForError()) {
			$this->inStore = false;
			return false;
		} else {
			$this->inStore = true;	
			$identifierFieldName = $this->entity()->identifierFieldName();
			$this->$identifierFieldName = $persistentStore->insertId();
		}
		
		if (!CDError::checkForError()) {
			return false;
		}
		
		return $this;
	}
	
	public function update() {
		$identifierFieldName = $this->entity()->identifierFieldName();
		$persistentStore = $this->persistentStore;
		
		$predicate = new CDPredicate($identifierFieldName, $this->$identifierFieldName);
	
		$table = $this->entity()->tableInString();
		$parameters = $this->getStringParametersFromData();
		$predicateString = $predicate->predicateInString();
	
		$query = sprintf("UPDATE %s %s %s LIMIT 1", $table, $parameters, $predicateString);
		
		$bool = $persistentStore->executeQuery($query);
		
		$connection = $persistentStore->connection;
		CDError::setCode($connection->errno, $connection->error);
		
		if (!CDError::checkForError()) {
			return false;
		} else {
			$this->inStore = true;
		}
		
		return $this;
	}

	public function delete() {
		$identifierFieldName = $this->entity()->identifierFieldName();
		$persistentStore = $this->persistentStore;
		
		$predicate = new CDPredicate($identifierFieldName, $this->$identifierFieldName);
	
		$table = $this->entity()->tableInString();
		$parameters = $this->getStringParametersFromData();
		$predicateString = $predicate->predicateInString();
	
		$query = sprintf("DELETE FROM %s %s LIMIT 1", $table, $predicateString);
		
		$bool = $persistentStore->executeQuery($query);
		
		$connection = $persistentStore->connection;
		CDError::setCode($connection->errno, $connection->error);
		
		if (!CDError::checkForError()) {
			return false;
		} else {
			$this->inStore = false;
		}
		
		return $this;
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
	 * Bool flag for object contains in store.
	 * 
	 * @access public
	 * @return void
	 */
	public function inStore() {
		return $this->inStore;
	}

}
