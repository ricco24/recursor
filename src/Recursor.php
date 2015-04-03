<?php

namespace App\Model\Recursor;

abstract class Recursor
{
	/** @var string|bool 			If we want use sub array for children */
	protected $subKey = false;

	/** @var array */
	protected $items = array();

	/** @var array */
	protected $children = array();

	/** @var array					Final generated list */
	protected $list = array();

	/** @var int					Max recursion */
	protected $maxDepth = false;

	/**
	 * @param $items
	 */
	public function __construct($items)
	{
		$this->items = $items;
	}

	/**
	 * Set maximal recursion depth
	 * @param int $maxDepth
	 */
	public function setMaxDepth($maxDepth)
	{
		$this->maxDepth = max(1, (int) $maxDepth);
	}

	/**
	 * @return array
	 */
	public function getList()
	{
		if (empty($this->list)) {
			$this->list = $this->build();
		}

		return $this->list;
	}

	/**
	 * Main build function
	 * @param null $key
	 * @return array
	 */
	protected function build($key = null)
	{
		$this->children = $this->buildChildren();
		$result = array();

		foreach ($this->items as $itemKey => $item) {
			if (!$this->isChildren($item, $itemKey, $key)) {
				continue;
			}

			$id = $this->getItemKey($item, $itemKey);
			$result[$id] = $this->getItemData($item, $itemKey, $this->getChildrenCount($id), 1);

			if ($this->subKey) {
				$result[$id][$this->subKey] = $this->callRecursive($id, $result[$id][$this->subKey]);
			} else {
				$result[$id] = $this->callRecursive($id, $result[$id]);
			}

			$this->afterItemHook($result[$id]);
		}

		$this->afterAllHook($result);

		return $result;
	}

	/**
	 * Returns children array, usable mainly for memory/time save
	 * @return array
	 */
	protected function buildChildren()
	{
		$result = array();
		foreach ($this->items as $key => $item) {
			$result[$this->getParentItemKey($item, $key)][$this->getItemKey($item, $key)] = $item;
		}
		return $result;
	}

	/**
	 * Check if item is child for given parent key
	 * @param $item
	 * @param $key
	 * @param null $parentKey
	 * @return bool
	 */
	protected function isChildren($item, $key, $parentKey = null)
	{
		return $this->getParentItemKey($item, $key) == $parentKey;
	}

	/**
	 * Counts item children
	 * @param $key
	 * @return int
	 */
	protected function getChildrenCount($key)
	{
		if (!isset($this->children[$key])) {
			return 0;
		}

		return count($this->children[$key]);
	}

	/**
	 * @param $key
	 * @param $result
	 * @param int $level
	 * @return mixed
	 */
	protected function callRecursive($key, &$result, $level = 2)
	{
		// Check max depth
		if ($this->maxDepth && $level > $this->maxDepth) {
			return $result;
		}

		// Check if item has children
		if (!isset($this->children[$key])) {
			return $result;
		}

		// Process all item children
		foreach ($this->children[$key] as $innerItemKey => $innerItem) {
			$isChildren = $this->isChildren($innerItem, $innerItemKey, $key);
			if (!$isChildren) {
				continue;
			}

			$id = $this->getItemKey($innerItem, $innerItemKey);
			$result[$id] = $this->getItemData($innerItem, $innerItemKey, $this->getChildrenCount($id), $level);

			if ($this->subKey) {
				$result[$id][$this->subKey] = $this->callRecursive($id, $result[$id][$this->subKey], $level + 1);
			} else {
				$result[$id] = $this->callRecursive($id, $result[$id], $level + 1);
			}

			$this->afterItemHook($result[$id]);
		}

		return $result;
	}

	/************************************* Hooks and abstracts **********************************sk*/

	/**
	 * Can modify item after build
	 * @param array $item
	 */
	protected function afterItemHook(&$item)
	{
		// Some optional code here
	}

	/**
	 * Can modify items array after build
	 * @param array $items
	 */
	protected function afterAllHook(&$items)
	{
		// Some optional code here
	}

	/**
	 * Returns item key
	 * @param $item
	 * @param $key
	 * @return mixed
	 */
	abstract protected function getItemKey($item, $key);

	/**
	 * Returns item parent key
	 * @param $item
	 * @param $key
	 * @return mixed
	 */
	abstract protected function getParentItemKey($item, $key);

	/**
	 * Returns item result data
	 * @param $item
	 * @param $key
	 * @param int $childrenCount
	 * @param int $level
	 * @return mixed
	 */
	abstract protected function getItemData($item, $key, $childrenCount, $level);
}