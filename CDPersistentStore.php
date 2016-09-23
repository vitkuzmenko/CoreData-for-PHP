<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CDPersistentStore.php
 * CDPersistentStore
 */

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class CDPersistentStore {
	
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
		return $this->connection->query($query);
	}
	
	public function insertId() {
		return $this->connection->insert_id;
	}

}
