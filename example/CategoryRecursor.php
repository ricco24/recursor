<?php

namespace Kelemen\Recursor;

class CategoryRecursor extends Recursor
{
	/** @var string			Sub key */
	protected $subKey = 'sub';

	/**
	 * Returns item key
	 * @param $item
	 * @param $key
	 * @return mixed
	 */
	protected function getItemKey($item, $key)
	{
		return $item['id'];
	}

	/**
	 * Returns item parent key
	 * @param $item
	 * @param $key
	 * @return mixed
	 */
	protected function getParentItemKey($item, $key)
	{
		return $item['parent_id'];
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
			'title' => $item['title'],
			'portal_id' => $item['portal_id'],
			'count' => 0,
			'sub' => []
		];
	}

	/**
	 * @param array $return
	 */
	public function afterItemHook(&$return)
	{
		$count = count($return['sub']);
		foreach ($return['sub'] as $sub) {
			$count += $sub['count'];
		}
		$return['count'] = $count;
	}
}