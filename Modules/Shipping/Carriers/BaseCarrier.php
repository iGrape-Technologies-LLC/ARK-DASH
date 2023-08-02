<?php
namespace Modules\Shipping\Carriers;

abstract class BaseCarrier {

	protected $id;
	protected $isKg;
	protected $name;

	public function getId() {
		return $this->id;
	}

	public function getIsKg() {
		return $this->isKg;
	}

	public function getName() {
		return $this->name;
	}

	public function configure() {
		
	}
}
?>
