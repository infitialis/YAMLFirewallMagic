<?php

abstract class Step4 extends logableBase {
	protected $chainsArray;
	protected $table;
	
	public function __construct($chainsArray){
		$this->chainsArray = $chainsArray;
		
		$this->_setTable();
	}
	
	abstract protected function _setTable();
	
	public function output(){
		$topOut = '';
		$bottomOut = '';
		foreach ($this->chainsArray[$this->table] as $t => $a){
			// First off, ass the definition:
			if (@$a['options']['default'] === true){
				if ( !empty($a['options']['policy']) ){
					$topOut .= ':' . $t . ' ' . $a['options']['policy'] . ' [0:0]' . "\n";
				} else {
					$this->logError('Error: Policy is empty on final compile, what\'s wrong?!', true);
				}
			} else {
				$topOut .= ':' . $t . ' - [0:0]' . "\n";
			}
			
			foreach ($a['rules'] as $rule){
				$bottomOut .= '-A ' . $t . ' ' . $rule . "\n";
			}
		}

		return $this->_header() . $topOut . $bottomOut . $this->_footer();
	}
	
	protected function _header(){
		return	'# Generated by YAMLFirewallMagic v1.0 on ' . date('D M j h:i:s A Y') . "\n" .
				'*' . $this->table . "\n";
	}
	
	protected function _footer(){
		return	'COMMIT' . "\n" .
				'# Completed on ' . date('D M j h:i:s A Y') . "\n";
	}
	
}