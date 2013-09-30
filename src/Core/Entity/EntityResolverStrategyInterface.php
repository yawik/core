<?php
/**
 * Cross Applicant Management
 *
 * @filesource
 * @copyright (c) 2013 Cross Solution (http://cross-solution.de)
 * @license   GPLv3
 */

/** Core models */
namespace Core\Entity;


/**
 * Model interface
 */
interface EntityResolverStrategyInterface 
{
    public function getEntityByStrategy($nameSpace);
  
}
