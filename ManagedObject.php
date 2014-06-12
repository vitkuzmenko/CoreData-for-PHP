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
	
	protected $persistentStore;
	
	protected $entity;
	
	protected $data;
	
	public $error = array();
	
	function __construct($persistentStore = null, $entity = null) {
		$this->persistentStore = $persistentStore;
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
	
	public function setDataFromArray(array $array = array()) {
		
		foreach ($array as $key => &$row) {
			$row = stripcslashes($row);
		}
		
		$this->data = $array;
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
			$this->$methodName($key, $value);
		} else {
			$this->setValueForKey($key, $value);
		}
	}
	
	public function getValueForKey($key) {
		return $this->data[$key];
	}
	
	public function __get($key) {
	
		$methodName = sprintf('get' . ucfirst($key));
		
		if (method_exists($this, $methodName)) {
			return $this->$methodName();
		} else {
			if (array_key_exists($key, $this->data)) {
				return $this->getValueForKey($key);
			} else {
				return null;
			}
		}
	}
	
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
	}
	
	private function parametersInString() {
		$predicate = new Predicate();
		$predicate->addEqualOperandFromArray($this->data);
		
		return $predicate->predicateInString(',', 'SET');
	}
	
	private function insert() {
		$table = $this->entity()->tableInString();
		$parameters = $this->parametersInString();
		
		$query = sprintf("INSERT INTO %s %s", $table, $parameters);
		
		$persistentStore = $this->persistentStore;
		$persistentStore->executeQuery($query);
		
		$identifierFieldName = $this->entity()->identifierFieldName();
		
		$this->$identifierFieldName = $persistentStore->insertId();
	}
	
	private function update() {
		$identifierFieldName = $this->entity()->identifierFieldName();
		
		$predicate = new Predicate($identifierFieldName, $this->$identifierFieldName);
	
		$table = $this->entity()->tableInString();
		$parameters = $this->parametersInString();
		$predicateString = $predicate->predicateInString();
	
		$query = sprintf("UPDATE %s %s %s LIMIT 1", $table, $parameters, $predicateString);
		
		$this->persistentStore->executeQuery($query);
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
