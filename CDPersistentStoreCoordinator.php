<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CDPersistentStoreCoordinator.php
 * CDPersistentStoreCoordinator
 */

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class CDPersistentStoreCoordinator {

	/**
	 * store
	 * Contains Persistent Stores
	 * 
	 * @var object
	 * @access private
	 */
	private $store = array();
	
	/**
	 * connected to MySQL
	 * 
	 * (default value: false)
	 * 
	 * @var bool
	 * @access private
	 */
	private $connected = false;
		
	/**
	 * connect function.
	 * - Connect to All Persistent Store Contains in $this->store
	 * 
	 * @access public
	 * @return void
	 */
	public function connect() {
		
		if ($this->connected) {
			return;
		}
		
		foreach ($this->store as $name => &$store) {
			
			@$this->connectToStore($store);
			
			if (CDError::checkForError()) {
				$this->connected = true;
			}
			
		}
		
	}
	
	/**
	 * addPersistentStore function.
	 * - Add New Persistent Store to Persistent Store Coordinator
	 * 
	 * @access public
	 * @param mixed $name
	 * @param mixed $store
	 * @return void
	 */
	public function addPersistentStore(CDPersistentStore $store) {
		$this->connected = false;
		
		array_push($this->store, $store);
	}
	
	public function getStoreByName($name) {
		foreach ($this->store as $store) {
			if ($name == $store->name) {
				return $store;
			}
		}
	}
	
	public function getStore($name = null) {
		if ($name) {
			return $this->getStoreByName($name);
		} else {
			if (count($this->store)) {
				return $this->store[0];
			}
		}
	}

	/**
	 * connectToStore function.
	 * - Connect to Persistent Store
	 * 
	 * @access private
	 * @param mixed $store
	 * @return CDPersistentStore
	 */
	private function connectToStore(&$store) {
		
		$newLink = (bool) count($this->store);
		
		$connection = new \mysqli($store->host, $store->user, $store->password, $store->dataBase);
		
		CDError::setCode($connection->connect_errno, $connection->connect_error);
		
		if (CDError::checkForError()) {
			
			$connection->set_charset($store->charset);
			
			$store->setConnection($connection);

		} else {
			
		}
		
		return $store;
	}
	
	/**
	 * selectDataBaseForStore function.
	 * 
	 * @access private
	 * @param mixed $store
	 * @return CDPersistentStore
	 */
	private function selectDataBaseForStore(&$store) {
		
		$error = 'Persistent Store ' . $name;
		
		$connection = $store->connection;
		
		CDError::setCode($connection->connect_errno, $connection->connect_error);
				
		return;
		
		$dataBase = $connection->select_db($store->dataBase);
		
		CDError::setCode($connection->connect_errno, $connection->connect_error);
		
		return $dataBase;
		
	}

}
