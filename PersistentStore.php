<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * PersistentStore.php
 * PersistentStore
 */

namespace CoreData;

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class PersistentStore {
	
	public $name;
	
	public $charset = 'utf8';
	
	public $host = 'localhost';
	
	public $user = 'root';
	
	public $password = 'root';
	
	public $dataBase;
	
	public $connection;
	
	function __construct ($name, $host, $user, $password, $dataBase, $charset = 'utf8') {
	
		$this->name = $name;
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->dataBase = $dataBase;
		$this->charset = $charset;
		
	}

	public function setConnection($connection) {
		$this->connection = $connection;
	}
	
	public function executeQuery($query) {
		return mysql_query($query, $this->connection);
	}
	
	public function insertId() {
		return mysql_insert_id($this->connection);
	}

}
