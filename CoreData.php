<?php

/*
 * Created 31/05/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * CoreData.php
 * 
 */
 
define('CORE_DATA_PATH', realpath(dirname(__FILE__)));

require_once CORE_DATA_PATH . '/PersistentStoreCoordinator.php';
require_once CORE_DATA_PATH . '/EntityDescription.php';
require_once CORE_DATA_PATH . '/FetchedResultsController.php';
require_once CORE_DATA_PATH . '/Predicate.php';
require_once CORE_DATA_PATH . '/SortDescriptor.php';
require_once CORE_DATA_PATH . '/ManagedObject.php';