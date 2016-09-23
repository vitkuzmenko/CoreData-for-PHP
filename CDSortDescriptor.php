<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CDSortDescriptor.php
 * CDSortDescriptor
 */

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class CDSortDescriptor {
	
	private $isASC;
	
	private $string;
	
	private $field;
	
	function __construct($field = 'id', $isASC = true) {
		$this->setField($field);
		$this->isASC = (bool) $isASC;
	}

	public function setField($field) {
		$this->field = trim($field);
	}

	public function setIsDesc() {
		$this->isASC = false;
	}

	public function setIsASC() {
		$this->isASC = true;
	}
	
	public function sortInString() {
		
		$ordering = '';
		
		if ($this->isASC) {
			$ordering = 'ASC';
		} else {
			$ordering = 'DESC';
		}
		
		$field = $this->field;
		
		if (CoreData::$isUnderscore) {
			$field = CDHelper::camelCaseToUnderscore($field);
		}
		
		$string = sprintf('ORDER BY `%s` %s', $field, $ordering);
		
		$this->string = $string;
		
		return $string;
	}

}
