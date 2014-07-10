<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2014 Cross Solution (http://cross-solution.de)
 * @license   AGPLv3
 */

/**  */ 
namespace Core\Form\Element;

use Zend\Form\Element\File;
use Zend\View\Helper\HelperInterface;
use Zend\Form\FormInterface;
/**
 *
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class FileUpload extends File implements ViewHelperProviderInterface
{
    protected $helper = 'formFileUpload';
    protected $form;
    
    public function setViewHelper($helper)
    {
        if (is_object($helper) && !$helper instanceOf HelperInterface) {
            throw new \InvalidArgumentException('Expects helper to be eiter a service name or an instance of "Zend\View\Helper\HelperInterface"');
        }
        
        $this->helper = $helper;
        return $this;
    }
    
    public function getViewHelper()
    {
        return $this->helper;
    }
    
    public function prepareElement(FormInterface $form)
    {
        $form->setAttribute('class', 'single-file-upload');
        $form->setAttribute('data-is-empty', null === $this->getValue());
        $this->form = $form;
        parent::prepareElement($form);
    }
    
    public function getFileEntity()
    {
        if (!$this->form || !($object = $this->form->getObject())) {
            return;
        }
        
        $entityName = $this->getName();
        
        try {
            $fileEntity = $object->$entityName;
        } catch (\OutOfBoundsException $e) {
            return null;
        }
        
        return $fileEntity;
    }
    
}