<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright https://yawik.org/COPYRIGHT.php
 */

/** */
namespace Core\Service\EntityEraser;

use Laminas\Filter\FilterInterface;

/**
 * Filter to map route parameters to repository names.
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test
 */
class NameFilter implements FilterInterface
{
    /**
     *
     *
     * @var array
     */
    private $map;

    /**
     * NameFilter constructor.
     *
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * Returns the mapped or unfiltered value.
     *
     * @param string $value
     *
     * @return string
     */
    public function filter($value)
    {
        return isset($this->map[$value]) ? $this->map[$value] : $value;
    }
}
