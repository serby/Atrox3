<?php 

class DataEntityException extends Exception {
	
	private $dataEntityObject;
	
	public function __construct(DataEntity $dataEntity, $message) {
		$this->dataEntityObject = $dataEntity;
		$this->message = $message;
	}
}