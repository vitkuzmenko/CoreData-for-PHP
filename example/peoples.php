<?php

/*
 * Created 12/06/14 by Vitaliy Kuz'menko © 2014
 * All rights reserved.

 * peoples.php
 * peoples
 */

namespace ManagedObject;

require_once '../ManagedObject.php';
require_once '../EntityDescription.php';

class peoples extends \CoreData\ManagedObject {
	
	public function entity() {
		if ($this->entity) {
			return $this->entity;
		}
		
		$entity = new \CoreData\EntityDescription('peoples', '\ManagedObject\peoples');
		
		$this->entity = $entity;
		
		return $entity;
	}
	
	public function getFamily() {
		$value = $this->getValueForKey('family');
		
		switch ($value) {
			case 1:
				return 'Не женат';
				break;
			case 2:
				return 'Встречаюсь';
				break;
			case 2:
				return 'Помолвлен';
				break;
			case 4:
				return 'Женат';
				break;
			case 5:
				return 'В поиске';
				break;		
		}
		
		return $value;
	}

}
