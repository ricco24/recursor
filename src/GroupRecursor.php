<?php

namespace Kelemen\Recursor;

class GroupRecursor extends Recursor
{
	/** @var string */
	protected $parentKey;

	/** @var string */
	protected $titleKey;

	/** @var string */
	protected $groupByKey;

	/** @var string				Only for inner use */
	protected $subKey = 'sub';

	/**
	 * @param $items
	 * @param string $groupByKey
	 * @param string $titleKey
	 * @param mixed $parentKey
	 */
	public function __construct($items, $groupByKey, $titleKey, $parentKey = null)
	{
		$this->groupByKey = $groupByKey;
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
		return [
			$this->groupByKey => $item[$this->groupByKey],
			$this->titleKey => str_repeat('-', $level - 1) . str_repeat(' ', min(1, $level - 1)) . $item[$this->titleKey],
			$this->subKey => []
		];

	}

	/**
	 * Move item children one level up
	 * @param array $return
	 */
	protected function afterItemHook(&$return)
	{
		foreach ($return[$this->subKey] as $subItemKey => $subItem) {
			foreach (array_reverse($subItem[$this->subKey], true) as $subSubItemKey => $subSubItem) {
				$this->insertAfter($return[$this->subKey], $subItemKey, [$subSubItemKey => $subSubItem]);
			}

			unset ($return[$this->subKey][$subItemKey][$this->subKey]);
		}
	}

	/**
	 * Group items by key
	 * @param array $items
	 */
	protected function afterAllHook(&$items)
	{
		$result = [];
		foreach ($items as $key => $item) {
			$result[$item[$this->groupByKey]][$key] = $item[$this->titleKey];
			foreach ($item[$this->subKey] as $subKey => $subItem) {
				$result[$item[$this->groupByKey]][$subKey] = $subItem[$this->titleKey];
			}
		}

		$items = $result;
	}

	/**
	 * Insert new array after specified key
	 * @param array $arr
	 * @param $key
	 * @param array $inserted
	 */
	private function insertAfter(array & $arr, $key, array $inserted)
	{
		$offset = array_search($key, array_keys($arr), true);
		$offset = $offset === false ? count($arr) : $offset + 1;
		$arr = array_slice($arr, 0, $offset, true) + $inserted + array_slice($arr, $offset, count($arr), true);
	}
}