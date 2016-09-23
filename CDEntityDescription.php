<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CDEntityDescription.php
 * CDEntityDescription
 */

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class CDEntityDescription {
	
	private $table;
	
	private $fields;
	
	public $identifierFieldName = 'id';
	
	private $managedObjectClass;
	
	function __construct ($table, $managedObjectClass = null, $fields = null) {
		$this->setTable($table);
		$this->setFields($fields);
		
		if (!$managedObjectClass) {
			$class = $table;
			if (CoreData::$isUnderscore) {
				$class = CDHelper::underscoreToCamelCase($class, true);
			}
			
			$managedObjectClass = sprintf('\%s', $class);
		}
		
		$this->setManagedOjectClass($managedObjectClass);
	}
	
	public static function identifierFieldName() {
		return 'id';
	}
	
	public function setTable($table) {
		
		$this->table = $table;
		
	}
	
	public function setManagedOjectClass($managedObjectClass) {
		if (!trim($managedObjectClass)) {
			$managedObjectClass = 'CDManagedObject';
		}
		$this->managedObjectClass = $managedObjectClass;
	}
	
	public function managedObjectClass() {
		return $this->managedObjectClass;
	}
	
	public function setFields($fields = null) {
		
		if (is_array($fields)) {
			$this->setFieldsFromArray($fields);
		} else if (is_string($fields)) {
			$this->setFieldsFromString($fields);
		} else {
			$this->fields = array();
		}
		
	}
	
	public function setFieldsFromArray(array $array) {
		
		$safeArray = array();
		
		$identifierField = false;
		
		foreach ($array as $key => $field) {
			
			$field = trim($field);
			
			$isNumeric = is_numeric($field);
			$isString  = is_string($field);
			
			if (!$isNumeric && $isString) {
				array_push($safeArray, $field);
			}
			
			if ($field == $this->identifierFieldName) {
				$identifierField = true;
			}
			
		}
		
		if ($identifierField) {
			array_push($safeArray, trim($this->identifierFieldName));
		}
		
		$this->fields = $safeArray;
		
	}
	
	public function setFieldsFromString($string) {
		
		$array = explode(',', $string);
		
		$this->setFieldsFromArray($array);
		
	}
	
	public function tableInString() {
		return sprintf("`%s`", $this->table);
	}
	
	public function fieldsInString($withTable = true) {
		
		if ($withTable) {
			return $this->fieldsInStringWithTable();
		} else {
			return $this->fieldsInStringWithoutTable();
		}
		
	}
	
	public function fieldsInStringWithTable() {
		
		$fieldsWithTableArray = array();
		
		foreach ($this->fields as $field) {
			
			$fieldWithTableString = sprintf("`%s`.`%s`", $this->table, $field);
			
			array_push($fieldsWithTableArray, $fieldWithTableString);
			
		}
					
		return $this->fieldsInSafeString($fieldsWithTableArray);
		
	}
	
	public function fieldsInStringWithoutTable() {
		
		$fieldsWithoutTableArray = array();
		
		foreach ($this->fields as $field) {
			
			$fieldWithoutTableString = sprintf("`%s`", $field);
			
			array_push($fieldsWithoutTableArray, $fieldWithoutTableString);
			
		}
		
		return $this->fieldsInSafeString($fieldsWithoutTableArray);
		
	}
	
	private function fieldsInSafeString($array) {
		
		if (count($array)) {
		
			return implode(', ', $array);
			
		} else {
		
			return sprintf('`%s`.*', $this->table);
			
		}
		
	}
	
}