<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * PersistentStoreCoordinator.php
 * PersistentStoreCoordinator
 */

namespace CoreData;

require_once realpath(dirname(__FILE__)) . '/CoreData.php';

class PersistentStoreCoordinator {

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
	 * error
	 * Contains Errors
	 * 
	 * @var array
	 * @access private
	 */
	public $error = array();
	
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
			
			$this->connectToStore($store);
			
			if (!$this->error) {
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
	public function addPersistentStore(PersistentStore $store) {
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
	 * @return PersistentStore
	 */
	private function connectToStore(&$store) {
		
		$newLink = (bool) count($this->store);
		
		$connection = new \mysqli($store->host, $store->user, $store->password, $store->dataBase);
		
		if ($connection) {
			
			$connection->set_charset($store->charset);
			
			$store->setConnection($connection);
			
		} else {
				
			array_push($this->error, $this->errorDescription($store, 400));
		}
		
		return $store;
	}
	
	/**
	 * selectDataBaseForStore function.
	 * 
	 * @access private
	 * @param mixed $store
	 * @return PersistentStore
	 */
	private function selectDataBaseForStore(&$store) {
		
		$error = 'Persistent Store ' . $name;
		
		$connection = $store->connection;
		
		if ($connection == false) {
		
			array_push($this->error, $this->errorDescription($store, 406));
		}
		
		$dataBase = $connection->select_db($store->dataBase);
		
		if ($dataBase == false) {
			
			array_push($this->error, $error . ' data base ' . $store->dataBase . ' not found.');
			
		}
		
		return $dataBase;
		
	}
	
	/**
	 * errorDescription function.
	 * - Error Description
	 * @access private
	 * @param mixed &$store
	 * @param mixed $code
	 * @return string
	 */
	private function errorDescription(&$store, $code) {
		
		$persistentStore = sprintf('Persistent Store %s', $store->name);
		
		switch ($code) {
			case 400:
				return sprintf('%s Connection Error: is not a valid username, password or host.', $persistentStore);
				break;
			case 406:
				return sprintf('%s Can not select data base. Connection is empty.', $persistentStore);
				break;
			case 404:
				return sprintf('%s Data Base %s not found', $persistentStore, $store->dataBase);
				break;
		}
		
	}

}
