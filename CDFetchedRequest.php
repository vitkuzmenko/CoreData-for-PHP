<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CDFetchedRequest.php
 * CDFetchedRequest
 */

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class CDFetchedRequest {
	
	// !Private Property
	
	private $entity;
	
	private $predicate;
	
	private $sortDescriptor;
	
	private $query;
	
	private $defaultLimit = 10;
	
	private $limit;
	
	private $page;
	
	// !Public Property
	
	public function __construct($entity = null, $predicate = null, $sortDescriptor = null, $limit = null) {
		$this->setEntity($entity);
		$this->setPredicate($predicate);
		$this->setSortDescriptor($sortDescriptor);
		$this->setLimit($limit);
	}
	
	public function setEntity($entity) {
		$this->entity = $entity;
	}
	
	public function entity() {
		return $this->entity;
	}
	
	public function managedObjectClass() {
		return $this->entity->managedObjectClass();
	}
	
	public function setPredicate($predicate) {
		$this->predicate = $predicate;
	}
	
	public function predicateInString() {
		
		$predicateString = null;
		
		if ((bool) $this->predicate) {
			$predicateString = $this->predicate->predicateInString();
		}
		return $predicateString;
	}
	
	// !Sort
	
	public function setSortDescriptor($sortDescriptor) {
		if (!$sortDescriptor) {
			$sortDescriptor = new CDSortDescriptor();
		}
		$this->sortDescriptor = $sortDescriptor;
	}
	
	// !Limit
	
	public function setLimit($limit) {
		if (is_array($limit)) {
			$this->setLimitWithRange($limit[0], $limit[1]);
		} else {
			$this->limit = intval($limit);
		}
	}
	
	public function setLimitWithRange($from, $count) {
		$this->limit = array(intval($from), intval($count));
	}
	
	public function setPage($number) {
		if (intval($this->limit) == 0) {
			$this->setLimit($this->defaultLimit);
		}
	
		$from = intval($this->limit) * intval($number - 1);
		
		$this->setLimitWithRange($from, $this->limit);
	}
	
	public function limitInString() {
		
		if ($this->limit == 0) {
			return;
		}
		
		$limit = null;
		
		if (is_array($this->limit)) {
		
			$from = $this->limit[0];
			$count = $this->limit[1];
			
			$limit = sprintf('LIMIT %d, %d', $from, $count);
			
		} else if (is_numeric($this->limit)) {
					
			$limit = sprintf('LIMIT %d', $this->limit);
			
		}
		
		return $limit;
	}
	
	// !Query
	
	private function selectInString() {
		$fields = $this->entity->fieldsInStringWithTable();
		$table = $this->entity->tableInString();
		
		return sprintf("SELECT %s FROM %s", $fields, $table);
	}

	public function selectCount() {
		$entity = $this->entity;
	
		$fields = $entity->fieldsInStringWithTable();
		$table = $entity->tableInString();
		
		$identifier = $entity->identifierFieldName;
		
		return sprintf("SELECT COUNT(%s) FROM %s", $identifier, $table);
	}
		
	public function querySelectInString() {
		return $this->queryInString($this->selectInString());
	}	

	public function querySelectCountInString() {
		return $this->queryInString($this->selectCount(), true);
	}	
	
	public function queryInString($base, $forCounting = false) {
	
		$array = array($base);
		
		$predicate = $this->predicateInString();
		
		if ((bool) $predicate) {
			array_push($array, $predicate);
		}
		
		$sort = $this->sortDescriptor->sortInString();
		
		if ((bool) $sort) {
			array_push($array, $sort);
		}
		
		if (!$forCounting) {
			$limit = $this->limitInString();
			
			if ((bool) $limit) {
				array_push($array, $limit);
			}			
		}
		
		$query = implode(' ', $array);
		
		$this->query = $query;
		
		return $query;
	}
	
}
