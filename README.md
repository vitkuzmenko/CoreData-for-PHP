CoreData for PHP
================

Objective-C CoreData Futures for PHP

# Connecton to Data Base

```php
$store = new \CoreData\PersistentStore('connectionName', 'host', 'user', 'password', 'dataBaseName');

$persistentStoreCoordinator = new \CoreData\PersistentStoreCoordinator;
$persistentStoreCoordinator->addPersistentStore($store);
$persistentStoreCoordinator->connect();
```
You also can connect to multiple data bases.

```php
$store1 = new \CoreData\PersistentStore('connectionName1', 'host1', 'user1', 'password1', 'dataBaseName1');
$store2 = new \CoreData\PersistentStore('connectionName2', 'host2', 'user2', 'password2', 'dataBaseName2');
$store3 = new \CoreData\PersistentStore('connectionName3', 'host3', 'user3', 'password3', 'dataBaseName2');

$persistentStoreCoordinator = new \CoreData\PersistentStoreCoordinator;
$persistentStoreCoordinator->addPersistentStore($store1);
$persistentStoreCoordinator->addPersistentStore($store2);
$persistentStoreCoordinator->addPersistentStore($store3);
$persistentStoreCoordinator->connect();
```

# Entity

Entity contains table name, fields.

```php
$table = 'user'; //required
$managedObjectClass = '\ManagedObject\class'; //optional
$fields = array('login', 'email', 'phone'); //optional

$entity = new \CoreData\EntityDescription($table, $managedObjectClass, $fields);
```