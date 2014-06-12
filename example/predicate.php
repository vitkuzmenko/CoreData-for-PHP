<?php

/*
require_once '../PersistentStore.php';
require_once '../PersistentStoreCoordinator.php';
require_once '../EntityDescription.php';
require_once '../FetchedResultsController.php';
require_once '../Predicate.php';
require_once '../SortDescriptor.php';
require_once '../ManagedObject.php';

$predicate = new \CoreData\Predicate;
$predicate->addEqualOperand('title', 'Hello');
$predicate->addEqualOperand('descr', 'Goodby');


$predicate2 = new \CoreData\Predicate;
$predicate2->addLikeOperandFromArray(array('title2' => 'Hello2', 'descr2' => 'Goodby2'));

$predicate->addORPredicate($predicate2);


$predicate->predicateInString();
?>

<pre>
	<?php print_r($predicate); ?>
</pre>
*/
