<?php
/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CDPredicate.php
 * CDPredicate
 */

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class CDPredicate {
	
	private $operand = array();
	
	private $operator;
	
	private $defaultOperator = 'AND';
	
	private $string;
	
	private $store;
	
	/**
	 * __construct function.
	 * Call if set parameters addEqualOperand($field, $value)
	 * 
	 * @access public
	 * @param mixed $field (default: null)
	 * @param mixed $value (default: null)
	 * @return void
	 */
	function __construct($field = null, $value = null, CDPersistentStore $store = null) {
		if (!$store) {
			$store = CoreData::getStore();
		}
		$this->store = $store;

		if ($field && !is_null($value)) {
			$this->addEqualOperand($field, $value);
		}
	}
	
	/**
	 * setOperator function.
	 * 
	 * @access public
	 * @param mixed $operator
	 * @return void
	 */
	public function setOperator($operator) {
		$this->operator = trim($operator);
	}
	
	/**
	 * addEqualOperand function.
	 * Add equal operand by field and value to predicate
	 * 
	 * @access public
	 * @param mixed $field
	 * @param mixed $value
	 * @return void
	 */
	public function addEqualOperand($field, $value) {
		$this->addSimpleOperand($field, $value, "`%s`='%s'");
	}

	/**
	 * addNotEqualOperand function.
	 * Add equal operand by field and value to predicate
	 * 
	 * @access public
	 * @param mixed $field
	 * @param mixed $value
	 * @return void
	 */
	public function addNotEqualOperand($field, $value) {
		$this->addSimpleOperand($field, $value, "`%s`!='%s'");
	}	
	
	public function addGreaterOperand($field, $value) {
		$this->addSimpleOperand($field, $value, "`%s` > '%s'");
	}

	public function addLessOperand($field, $value) {
		$this->addSimpleOperand($field, $value, "`%s` < '%s'");
	}
	
	public function addBetweenOperand($field, array $value) {
		$value = implode(' AND ', $value);
		$this->addSimpleOperand($field, $value, "(`%s` BETWEEN %s)");
	}
	
	public function addSimpleOperand($field, $value, $format) {
		if (is_array($value)) {
			if (array_key_exists('value', $value)) {
				$value = $value['value'];
			} else {
				$value = null;
			}
		}
		
		if (is_string($value)) {

			$store = $this->store;
			$connection = $store->connection;
			
			$value = $connection->real_escape_string($value);
		}
		
		if (CoreData::$isUnderscore) {
			$field = CDHelper::camelCaseToUnderscore($field);
		}
		
		array_push($this->operand, sprintf($format, $field, $value));
	}
	
	/**
	 * addEqualOperandFromArray function.
	 * Add equal operand by key => value array
	 * 
	 * @access public
	 * @param array $array
	 * @return void
	 */
	public function addEqualOperandFromArray(array $array, $excludeField = null) {
		foreach ($array as $field => $value) {
			
			if ((is_array($excludeField) && in_array($field, $excludeField))
			|| (is_string($excludeField) && $excludeField == $field)) {
				continue;
			}
			
			$this->addEqualOperand($field, $value);
		}
	}

	/**
	 * addLikeOperand function.
	 * Add LIKE operand by field and value to predicate
	 * 
	 * @access public
	 * @param mixed $field
	 * @param mixed $value
	 * @return void
	 */
	public function addLikeOperand($field, $value) {
		$value = '%' . $value . '%';
		$this->addSimpleOperand($field, $value, "`%s` LIKE '%s'");
	}
	
	/**
	 * addLikeOperandFromArray function.
	 * Add LIKE operand by key => value array
	 * 
	 * @access public
	 * @param array $array
	 * @return void
	 */
	public function addLikeOperandFromArray(array $array) {
		foreach ($array as $field => $value) {
			$this->addLikeOperand($field, $value);
		}
	}
	
	/**
	 * addInOperand function.
	 * Add IN(1,2,3)
	 * 
	 * @access public
	 * @param mixed $field
	 * @param array $value (default: array())
	 * @return void
	 */
	public function addInOperand($field, array $array = array()) {

		foreach ($array as $key => $value) {
			if (is_numeric($value)) {
				$array[$key] = intval($value);
			} else if (is_string($value)) {
				$array[$key] = sprintf("'%s'", $value);
			} else if (is_array($value)) {
				unset($array[$key]);
			}
		}

		if (count($array)) {
			$this->addSimpleOperand($field, implode(',', $array), "`%s` IN(%s)");
		}
	}
		
	/**
	 * addANDPredicate function.
	 * 
	 * @access public
	 * @param mixed $predicate
	 * @return void
	 */
	public function addAndPredicate($predicate) {
		$this->addPredicate($predicate, 'AND');
	}
	
	/**
	 * addORPredicate function.
	 * 
	 * @access public
	 * @param mixed $predicate
	 * @return void
	 */
	public function addOrPredicate($predicate) {
		$this->addPredicate($predicate, 'OR');
	}
	
	/**
	 * addPredicate function.
	 * 
	 * @access public
	 * @param mixed $predicate
	 * @param string $operator (default: 'AND')
	 * @return void
	 */
	public function addPredicate($predicate, $operator = null) {
		
		$string = $predicate->predicateInString();
		
		if (!empty($string)) {
			$string = '(' . $predicate->predicateInString($operator, null) . ')';			
			array_push($this->operand, $string);
		}
	}
	
	/**
	 * predicateInString function.
	 * 
	 * @access public
	 * @param string $operator (default: 'AND')
	 * @return string
	 */
	public function predicateInString($operator = null, $controlWord = 'WHERE') {
	
		if ($controlWord != 'WHERE' && $controlWord != 'SET' && !is_null($controlWord)) {
			Error::setError('Predicate Error: is not a valid control word.');
			return;
		}
	
		$string = null;
		
		if (count($this->operand)) {
			if (trim($operator)) {
				
			} else if (trim($this->operator)) {
				$operator = $this->operator;
			} else {
				$operator = $this->defaultOperator;
			}
			
			$string = implode(' ' . $operator . ' ', $this->operand);
			$string = sprintf('%s %s', $controlWord, $string);
		}
	
		$this->string = $string;
		
		return $string;
	}
	
}
