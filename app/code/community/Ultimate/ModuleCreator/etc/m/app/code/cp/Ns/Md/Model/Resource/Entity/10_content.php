	/**
	 * Retrieve {{entitiesLabel}}
	 * @access public
	 * @param integer $parent
	 * @param integer $recursionLevel
	 * @param boolean|string $sorted
	 * @param boolean $asCollection
	 * @param boolean $toLoad
	 * @return Varien_Data_Tree_Node_Collection|{{Namespace}}_{{Module}}_Model_Resource_{{Entity}}_Collection
	 * {{qwertyuiop}}
	 */
	public function get{{Entities}}($parent, $recursionLevel = 0, $sorted = false, $asCollection = false, $toLoad = true){
		$tree = Mage::getResourceModel('{{module}}/{{entity}}_tree');
		$nodes = $tree->loadNode($parent)
			->loadChildren($recursionLevel)
			->getChildren();
		$tree->addCollectionData(null, $sorted, $parent, $toLoad, true);
		if ($asCollection) {
			return $tree->getCollection();
		}
		return $nodes;
	}
	/**
	 * Return all children ids of {{entity}} (with {{entity}} id)
	 * @access public
	 * @param {{Namespace}}_{{Module}}_Model_{{Entity}} ${{entity}}
	 * @return array
	 * {{qwertyuiop}}
	 */
	public function getAllChildren(${{entity}}){
		$children = $this->getChildren(${{entity}});
		$myId = array(${{entity}}->getId());
		$children = array_merge($myId, $children);
		return $children;
	}
	/**
	 * Check {{entityLabel}} is forbidden to delete.
	 * @access public
	 * @param integer ${{entity}}Id
	 * @return boolean
	 * {{qwertyuiop}}
	 */
	public function isForbiddenToDelete(${{entity}}Id){
		return (${{entity}}Id == Mage::helper('{{module}}/{{entity}}')->getRoot{{Entity}}Id());
	}
	/**
	 * Get {{entityLabel}} path value by its id
	 * @access public
	 * @param int ${{entity}}Id
	 * @return string
	 * {{qwertyuiop}}
	 */
	public function get{{Entity}}PathById(${{entity}}Id){
		$select = $this->getReadConnection()->select()
			->from($this->getMainTable(), array('path'))
			->where('entity_id = :entity_id');
		$bind = array('entity_id' => (int)${{entity}}Id);
		return $this->getReadConnection()->fetchOne($select, $bind);
	}
	/**
	 * Move {{entityLabel}} to another parent node
	 * @access public
	 * @param {{Namespace}}_{{Module}}_Model_{{Entity}} ${{entity}}
	 * @param {{Namespace}}_{{Module}}_Model_{{Entity}} $newParent
	 * @param null|int $after{{Entity}}Id
	 * @return {{Namespace}}_{{Module}}_Model_Resource_{{Entity}}
	 * {{qwertyuiop}}
	 */
	public function changeParent({{Namespace}}_{{Module}}_Model_{{Entity}} ${{entity}}, {{Namespace}}_{{Module}}_Model_{{Entity}} $newParent, $after{{Entity}}Id = null){
		$childrenCount  = $this->getChildrenCount(${{entity}}->getId()) + 1;
		$table  		= $this->getMainTable();
		$adapter		= $this->_getWriteAdapter();
		$levelFiled 	= $adapter->quoteIdentifier('level');
		$pathField  	= $adapter->quoteIdentifier('path');
		
		/**
		 * Decrease children count for all old {{entityLabel}} parent {{entitiesLabel}}
		 */
		$adapter->update(
			$table,
			array('children_count' => new Zend_Db_Expr('children_count - ' . $childrenCount)),
			array('entity_id IN(?)' => ${{entity}}->getParentIds())
		);
		/**
		 * Increase children count for new {{entityLabel}} parents
		 */
		$adapter->update(
			$table,
			array('children_count' => new Zend_Db_Expr('children_count + ' . $childrenCount)),
			array('entity_id IN(?)' => $newParent->getPathIds())
		);
		
		$position = $this->_processPositions(${{entity}}, $newParent, $after{{Entity}}Id);
		
		$newPath  = sprintf('%s/%s', $newParent->getPath(), ${{entity}}->getId());
		$newLevel = $newParent->getLevel() + 1;
		$levelDisposition = $newLevel - ${{entity}}->getLevel();
		
		/**
		 * Update children nodes path
		 */
		$adapter->update(
			$table,
			array(
				'path' => new Zend_Db_Expr('REPLACE(' . $pathField . ','.
					$adapter->quote(${{entity}}->getPath() . '/'). ', '.$adapter->quote($newPath . '/').')'
				),
				'level' => new Zend_Db_Expr( $levelFiled . ' + ' . $levelDisposition)
			),
			array($pathField . ' LIKE ?' => ${{entity}}->getPath() . '/%')
		);
		/**
		 * Update moved {{entityLabel}} data
		 */
		$data = array(
			'path'  => $newPath,
			'level' => $newLevel,
			'position'  =>$position,
			'parent_id' =>$newParent->getId()
		);
		$adapter->update($table, $data, array('entity_id = ?' => ${{entity}}->getId()));
		// Update {{entityLabel}} object to new data
		${{entity}}->addData($data);
		return $this;
	}
	/**
	 * Process positions of old parent {{entityLabel}} children and new parent {{entityLabel}} children.
	 * Get position for moved {{entityLabel}}
	 * @access protected
	 * @param {{Namespace}}_{{Module}}_Model_{{Entity}} ${{entity}}
	 * @param {{Namespace}}_{{Module}}_Model_{{Entity}} $newParent
	 * @param null|int $after{{Entity}}Id
	 * @return int
	 * {{qwertyuiop}}
	 */
	protected function _processPositions(${{entity}}, $newParent, $after{{Entity}}Id){
		$table  = $this->getMainTable();
		$adapter= $this->_getWriteAdapter();
		$positionField  = $adapter->quoteIdentifier('position');
		
		$bind = array(
			'position' => new Zend_Db_Expr($positionField . ' - 1')
		);
		$where = array(
			'parent_id = ?' => ${{entity}}->getParentId(),
			$positionField . ' > ?' => ${{entity}}->getPosition()
		);
		$adapter->update($table, $bind, $where);
		
		/**
		 * Prepare position value
		 */
		if ($after{{Entity}}Id) {
			$select = $adapter->select()
				->from($table,'position')
				->where('entity_id = :entity_id');
			$position = $adapter->fetchOne($select, array('entity_id' => $after{{Entity}}Id));
			$bind = array(
				'position' => new Zend_Db_Expr($positionField . ' + 1')
			);
			$where = array(
				'parent_id = ?' => $newParent->getId(),
				$positionField . ' > ?' => $position
			);
			$adapter->update($table, $bind, $where);
		} 
		elseif ($after{{Entity}}Id !== null) {
			$position = 0;
			$bind = array(
				'position' => new Zend_Db_Expr($positionField . ' + 1')
			);
			$where = array(
				'parent_id = ?' => $newParent->getId(),
				$positionField . ' > ?' => $position
			);
			$adapter->update($table, $bind, $where);
		} 
		else {
			$select = $adapter->select()
				->from($table,array('position' => new Zend_Db_Expr('MIN(' . $positionField. ')')))
				->where('parent_id = :parent_id');
			$position = $adapter->fetchOne($select, array('parent_id' => $newParent->getId()));
		}
		$position += 1;
		return $position;
	}
