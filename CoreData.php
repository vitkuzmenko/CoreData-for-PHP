<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CoreData.php
 * 
 */
 
define('CORE_DATA_PATH', realpath(dirname(__FILE__)));

require_once CORE_DATA_PATH . '/PersistentStoreCoordinator.php';
require_once CORE_DATA_PATH . '/PersistentStore.php';
require_once CORE_DATA_PATH . '/EntityDescription.php';
require_once CORE_DATA_PATH . '/FetchedRequest.php';
require_once CORE_DATA_PATH . '/FetchedResultsController.php';
require_once CORE_DATA_PATH . '/Predicate.php';
require_once CORE_DATA_PATH . '/SortDescriptor.php';
require_once CORE_DATA_PATH . '/ManagedObject.php';

/**
 * Singleton CoreData class
 *
 */
final class CoreData {
	
	public $coordinator;
	
    /**
     * Call this method to get singleton
     *
     * @return CoreData
     */
    public static function sharedInstance() {
        static $inst = null;
        if ($inst === null) {
            $inst = new CoreData();
        }
        return $inst;
    }
    
    /**
     * Get store by name or first store.
     * 
     * @access public
     * @static
     * @param mixed $name (default: null)
     * @return void
     */
    public static function getStore($name = null) {
    	$coordinator = CoreData::coordinator();
    	return $coordinator->getStore($name);
    }
    
    /**
     * Get PersistentStoreCoordinator
     * 
     * @access public
     * @return \CoreData\PersistentStoreCoordinator
     */
    public static function coordinator() {
	    $coreData = CoreData::sharedInstance();
	    
    	if ($coreData->coordinator) {
	    	return $coreData->coordinator;
    	}
    	
    	$coreData->coordinator = new \CoreData\PersistentStoreCoordinator();
    	
    	return $coreData->coordinator;
    }
    
    // ! AddStores
    
    /**
     * Add Store to PersistentStoreCoordinator.
     * 
     * @access public
     * @static
     * @param \CoreData\PersistentStore $store
     * @return void
     */
    public static function addStore(\CoreData\PersistentStore $store) {
    	$coordinator = CoreData::coordinator();
    	$coordinator->addPersistentStore($store);
    }
    
    public static function addStoreFromArray(array $store) {
		$newStore = new \CoreData\PersistentStore($store['name'], $store['host'], $store['user'], $store['password'], $store['dataBase']);
		CoreData::addStore($newStore);
    }
    
    public static function connect() {
    	$coordinator = CoreData::coordinator();
    	$coordinator->connect();
    }
    
    public static function error() {
    	$coordinator = CoreData::coordinator();
    	return $coordinator->error;
    }
    
}