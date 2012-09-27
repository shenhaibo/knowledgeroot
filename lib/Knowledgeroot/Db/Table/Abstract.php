<?php

/**
 *
 */

/**
 *
 */
abstract class Knowledgeroot_Db_Table_Abstract extends Zend_Db_Table_Abstract {

    public function __construct($config = array(), $definition = null) {

	parent::__construct($config, $definition);

	$this->_setSequence();
    }

    protected function _setSequence() {
	if ($this->_db instanceof Zend_Db_Adapter_Pdo_Mysql || $this->_db instanceof Zend_Db_Adapter_Mysqli) {
	    $this->_sequence = (bool) $this->_sequence;
	}

	return $this;
    }

}

?>