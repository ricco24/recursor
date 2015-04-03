<?php

namespace App\Model\Recursor;

class NodeListRecursor extends Recursor
{
	/** @var string */
	protected $parentKey;

	/** @var string */
	protected $titleKey;

	/**
	 * @param $items
	 * @param string $titleKey
	 * @param string $parentKey
	 */
	public function __construct($items, $titleKey, $parentKey = null)
	{
		$this->parentKey = $parentKey;
		$this->titleKey = $titleKey;
		parent::__construct($items);
	}

	/**
	 * Returns item key
	 * @param $item
	 * @param $key
	 * @return mixed
	 */
	protected function getItemKey($item, $key)
	{
		return $key;
	}

	/**
	 * Returns item parent key
	 * @param $item
	 * @param $key
	 * @return mixed
	 */
	protected function getParentItemKey($item, $key)
	{
		return $this->parentKey
					? $item[$this->parentKey]
					: null;
	}

	/**
	 * Returns item result data
	 * @param $item
	 * @param $key
	 * @param int $childrenCount
	 * @param int $level
	 * @return array
	 */
	protected function getItemData($item, $key, $childrenCount, $level)
	{
		if ($childrenCount) {
			return [];
		}

		return $item[$this->titleKey];
	}
}