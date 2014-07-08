<?php

include_once '../PersistentStore.php';
include_once '../PersistentStoreCoordinator.php';
include_once '../EntityDescription.php';
include_once '../FetchedRequest.php';
include_once '../FetchedResultsController.php';
include_once '../Predicate.php';
include_once '../SortDescriptor.php';
include_once '../ManagedObject.php';
include_once 'peoples.php';

print('<pre>');

$okyes = new \CoreData\PersistentStore('okyes', 'localhost', 'root', 'root', 'okyes');

$persistentStoreCoordinator = new \CoreData\PersistentStoreCoordinator;
$persistentStoreCoordinator->addPersistentStore($okyes);
$persistentStoreCoordinator->connect();

//============

$entity = new \CoreData\EntityDescription('peoples', '\ManagedObject\peoples');

$predicate = new \CoreData\Predicate();
$predicate->addEqualOperand('id', 1);

$sortDescriptor = new \CoreData\SortDescriptor('id', false);

$limit = 10;

$fetchedRequest = new \CoreData\FetchedRequest($entity, $predicate, $sortDescriptor, $limit);


$fetchedResultsController = new \CoreData\FetchedResultsController($okyes, $fetchedRequest);
$fetchedObjects = $fetchedResultsController->performFetch();

$object = $fetchedResultsController->firstObject();

$object->first_name = "Виталик";

$object->save();
