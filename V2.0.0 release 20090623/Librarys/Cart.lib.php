<?php

class SCN_Cart
{
	function SCN_Cart()
	{
		$this->_curCart = &$_SESSION[APP_KEY]['CUR_CART'];
	}
	
	function add($id, $quantity, $size)
	{
		$this->_curCart[$id] = array($quantity, $size);
	}
	
	function modify($id, $quantity, $size)
	{
		if (isset($this->_curCart[$id])) {
			$this->add($id, $quantity, $size);
		}
	}
	
	function delete($ids)
	{
		if (is_array($ids)) {
			foreach ($ids as $id) {
				$this->delete($id);
			}
		} else {
			$this->_curCart[$id] = NULL;
			unset($this->_curCart[$id]);
		}
	}
	
	function getQuantityById($id)
	{
		return $this->_curCart[$id][0];
	}
	
	function getSizeById($id)
	{
		return $this->_curCart[$id][1];
	}
	
	function getId()
	{
		return array_keys($this->_curCart);
	}
	
	function clear()
	{
		$this->_curCart = array();
		unset($this->_curCart);
	}
}

?>