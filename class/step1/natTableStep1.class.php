<?php

class natTableStep1 extends Step1 {
	
	protected function _parse(){
		$this->_defaultChains();
		$this->_ipChains();
		$this->_otherChains();
	}
	
	private function _validDefaultPolicy($policy){
		switch ($policy){
			case 'ACCEPT':
				return true;
				break;
			default:
				return false;
				break;
		}
	}
	
	private function _defaultChains(){
		if ( @is_array( $this->pdata['default-chains'] ) ){
			foreach ( $this->pdata['default-chains'] as $dchain ){
				if (@$dchain['name'] == 'PREROUTING'){
					if (!empty($dchain['policy'])){
						if ($this->_validDefaultPolicy($dchain['policy'])){
							$this->dataArray['tables']['nat']['default-chains']['PREROUTING']['policy'] = $dchain['policy'];
						} else {
							$this->logError('Error: invalid policy for PREROUTING chain (NAT) - ' . $dchain['policy'], true);
						}
					} else {
						$this->logError('Warning: policy not defined for default chain PREROUTING (NAT) - using default of DROP', false);
					}
					if ( @is_array($dchain['rules']) ){
						foreach ($dchain['rules'] as $rule){
							$this->dataArray['tables']['nat']['default-chains']['PREROUTING']['rules'][] = $rule;
						}
					} else {
						$this->logError('Warning: rules array for default chain PREROUTING (NAT) doesn\'t exist.', false);
					}
				} else if (@$dchain['name'] == 'INPUT'){
					if (!empty($dchain['policy'])){
						if ($this->_validDefaultPolicy($dchain['policy'])){
							$this->dataArray['tables']['nat']['default-chains']['INPUT']['policy'] = $dchain['policy'];
						} else {
							$this->logError('Error: invalid policy for INPUT chain (NAT) - ' . $dchain['policy'], true);
						}
					} else {
						$this->logError('Warning: policy not defined for default chain INPUT (NAT) - using default of DROP', false);
					}
					if ( @is_array($dchain['rules']) ){
						foreach ($dchain['rules'] as $rule){
							$this->dataArray['tables']['nat']['default-chains']['INPUT']['rules'][] = $rule;
						}
					} else {
						$this->logError('Warning: rules array for default chain INPUT (NAT) doesn\'t exist.', false);
					}
				} else if (@$dchain['name'] == 'OUTPUT'){
					if (!empty($dchain['policy'])){
						if ($this->_validDefaultPolicy($dchain['policy'])){
							$this->dataArray['tables']['nat']['default-chains']['OUTPUT']['policy'] = $dchain['policy'];
						} else {
							$this->logError('Error: invalid policy for OUTPUT chain (NAT) - ' . $dchain['policy'], true);
						}
					} else {
						$this->logError('Warning: policy not defined for default chain OUTPUT (NAT) - using default of ACCEPT', false);
					}
					if ( @is_array($dchain['rules']) ){
						foreach ($dchain['rules'] as $rule){
							$this->dataArray['tables']['nat']['default-chains']['OUTPUT']['rules'][] = $rule;
						}
					} else {
						$this->logError('Warning: rules array for default chain OUTPUT (NAT) doesn\'t exist.', false);
					}
				} else if (@$dchain['name'] == 'POSTROUTING'){
					if (!empty($dchain['policy'])){
						if ($this->_validDefaultPolicy($dchain['policy'])){
							$this->dataArray['tables']['nat']['default-chains']['POSTROUTING']['policy'] = $dchain['policy'];
						} else {
							$this->logError('Error: invalid policy for POSTROUTING chain (NAT) - ' . $dchain['policy'], true);
						}
					} else {
						$this->logError('Warning: policy not defined for default chain POSTROUTING (NAT) - using default of ACCEPT', false);
					}
					if ( @is_array($dchain['rules']) ){
						foreach ($dchain['rules'] as $rule){
							$this->dataArray['tables']['nat']['default-chains']['POSTROUTING']['rules'][] = $rule;
						}
					} else {
						$this->logError('Warning: rules array for default chain POSTROUTING (NAT) doesn\'t exist.', false);
					}
				} else {
					$this->logError( 'Invalid Default Chain - ' . var_export($dchain, true), false);
				}
			}
		} else {
			$this->logError( 'Warning: default chains block doesn\'t exist for NAT table.', false);
		}
	}
	
	private function _ipChains(){
		if ( @is_array( $this->pdata['ip-chains'] ) ){
			foreach ( $this->pdata['ip-chains'] as $ipchain ){
				if ( !empty($ipchain['in-iface']) ){
					if ( in_array($ipchain['in-iface'], array_keys($this->dataArray['interfaces'])) ){
						if ( @filter_var($ipchain['ip-address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ){
							if ( @is_array($ipchain['rules']) ){
								$chain = array( 'in-iface' => $ipchain['in-iface'], 'ip-address' => $ipchain['ip-address'], 'rules' => array() );
								foreach ($ipchain['rules'] as $rule){
									$chain['rules'][] = $rule;
								}
								$this->dataArray['tables']['nat']['ip-chains'][] = $chain;
							} else {
								$this->logError( 'Warning: ip-chain (NAT) doesn\'t contain a rules array.', false);
							}
						} else {
							$this->logError( 'Error: Invalid IP Address for ip-chain (NAT) - ' . $ipchain['ip-address'], true);
						}
					} else {
						$this->logError( 'Error: Invalid interface specified in ip-chain (NAT) - ' . $ipchain['in-iface'], true);
					}
				} else {
					$this->logError( 'Error: in-iface not specified in ip-chain (NAT)', true);
				}
			}
		}
	}
	
	private function _otherChains(){
		if ( @is_array( $this->pdata['other-chains'] ) ){
			foreach( $this->pdata['other-chains'] as $ochain ){
				if ( !empty( $ochain['name'] ) ){
					if ( ! $this->_chkChainLen($ochain['name']) ){
						$this->logError('Error: other-chain (NAT) name longer than 30 chars', true);
					}
					if ( ! @is_array($this->dataArray['tables']['nat']['other-chains'][$ochain['name']]) ){
						if ( @is_array( $ochain['rules'] ) ){
							$this->dataArray['tables']['nat']['other-chains'][$ochain['name']] = array('default-goto' => 'ACCEPT', 'rules' => array());
							foreach ( $ochain['rules'] as $rule ){
								$this->dataArray['tables']['nat']['other-chains'][$ochain['name']]['rules'][] = $rule;
							}
							if ( !empty( $ochain['default-goto'] ) ){
								$this->dataArray['tables']['nat']['other-chains'][$ochain['name']]['default-goto'] = $ochain['default-goto'];
							} else {
								$this->logError( 'Warning: using default-goto of ACCEPT for other-chain (NAT) as none specified!', false);
							}
						} else {
							$this->logError( 'Error: rules array missing from other chain (NAT) - ' . var_dump($ochain, true), true);
						}
					} else {
						$this->logError( 'Error: \'other\' chain (NAT) with this name already exists - ' . var_dump($ochain, true), true);
					}
				} else {
					$this->logError( 'Error: you must specify a name for a other chain (NAT) - ' . var_dump($ochain, true), true);
				}
			}
		} else {
			$this->logError( 'Warning: service chains block (NAT) doesn\'t exist.', false);
		}
	}
	
}