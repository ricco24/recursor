# Recursor
Library build recursive array from one level array with some recursive dependency.

####Input array example
```php
$categories = [
	1 => [
		'title' => 'Sport',
		'portal' => 'TopSport',
		'parent_id' => null,
	],
	2 => [
		'title' => 'Hockey',
		'portal' => 'TopSport',
		'parent_id' => 1,
	],
	3 => [
		'title' => 'Football',
		'portal' => 'TopSport',
		'parent_id' => 1,
	],
	4 => [
		'title' => 'World',
		'portal' => 'BestNews',
		'parent_id' => null,
	]
];
```

## NodeListRecursor
Only lists (elements without children) has title, nodes are arrays with children.
```php
use App\Model\Recursor\NodeListRecursor;

$recursor = new NodeListRecursor($categories, 'title', 'parent_id');
# sets maximal recursion depth
$list->setMaxDepth(3);
# get final recursive array  
$list = $recursor->getList();

# result
array(2) {
  [1] => array(2) {
    [2] => string(6) "Hockey"
    [3] => string(8) "Football"
  }
  [4] => string(5) "World"
}
```

## GroupRecursor
Very usefull to generate array for form selects.
```php
use App\Model\Recursor\GroupRecursor;

$recursor = new GroupRecursor($categories, 'portal', 'title', 'parent_id');
$list = $recursor->getList();

# result
array(2) {
  ["TopSport"] => array(3) {
    [1] => string(5) "Sport"
    [2] => string(7) "- Hockey"
    [3] => string(9) "- Football"
  }
  ["BestNews"] => array(1) {
    [4] => string(5) "World"
  }
}
```

## Custom recursor
You can create recursor that fits exact you needs. You need to implement 3 abstract methods.

```php
# Used as item key in result array
abstract protected function getItemKey($item, $key);

# Get parent key for given item
abstract protected function getParentItemKey($item, $key);

# Returns result item structure as array
abstract protected function getItemData($item, $key, $childrenCount, $level);
```

Every recursor can use 2 hook methods
```php
# Called after build every item
protected function afterItemHook(&$item);

# Called after build result array
protected function afterAllHook(&$items);
```

If you want to create custom recursor with children in result you need to define **$subKey** class parameter. This param needs to be used in **getItemData()** method as array.

### Example of custom recursor
This recursor counts recursive item children.
```php
namespace App\Model\Recursor;

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
			$this->subKey => []
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
```