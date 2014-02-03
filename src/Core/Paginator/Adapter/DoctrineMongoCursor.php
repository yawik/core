<?php
/**
 * Cross Applicant Management
 *
 * @filesource
 * @copyright (c) 2013 Cross Solution (http://cross-solution.de)
 * @license   AGPLv3
 */

/** DoctrineMongoCursor.php */ 
namespace Core\Paginator\Adapter;

use Zend\Paginator\Adapter\AdapterInterface;
use Doctrine\ODM\MongoDB\Cursor;

/**
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Roman Konz <roman@konz.me>
 */
class DoctrineMongoCursor implements AdapterInterface
{
    /**
     * @var Doctrine\ODM\MongoDB\Cursor
     */
    protected $cursor;

    /**
     * Constructor
     *
     * @param Cursor $cursor
     */
    public function __construct(Cursor $cursor)
    {
        $this->cursor = $cursor;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return $this->cursor->count();
    }

    /**
     * {@inheritDoc}
     */
    public function getItems($offset, $itemCountPerPage)
    {
        return $this->cursor
                    ->skip($offset)
                    ->limit($itemCountPerPage)
                    ->toArray();
        
    }
}

