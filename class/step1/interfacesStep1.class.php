<?php

class InterfacesStep1 extends logableBase {
	private $dataArray;
	private $pdata;
	
	public function __construct($parsed, &$dataArray){
		$this->dataArray =& $dataArray;
		$this->pdata = $parsed;
		
		$this->_parse();
	}
	
	private function _parse(){
		if ( is_array($this->pdata['interfaces']) ){
			foreach ($this->pdata['interfaces'] as $rif){
				if (( !empty($rif['ifname']) ) && ( !empty($rif['osifname']) )){
					$this->dataArray['interfaces'][$rif['ifname']] = $rif['osifname'];
				} else {
					$this->logError('Invalid Interface Layout' . var_export($rif, true) );
				}
			}
		} else {
			$this->logError('No Interfaces Array Found!', true);
		}
	}
	
}