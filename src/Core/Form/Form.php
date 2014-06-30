<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2104 Cross Solution (http://cross-solution.de)
 * @license   AGPLv3
 */

namespace Core\Form;

use Zend\Form\Form as ZendForm;
use Zend\Form\FieldsetInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\InputFilter\InputProviderInterface;
use Core\Entity\Hydrator\EntityHydrator;

class Form extends ZendForm
{
    
    protected $baseFieldset;
    protected $params;
    
    public function getHydrator() {
        if (!$this->hydrator) {
            $hydrator = new EntityHydrator();
            $this->addHydratorStrategies($hydrator);
            $this->setHydrator($hydrator);
        }
        return $this->hydrator;
    }
    
    public function init()
    {
        //$this->addParameterFields();
        $this->addBaseFieldset();
        $this->addButtonsFieldset();
    }
    
    protected function addParametersFields()
    {
        foreach ($this->params as $name => $value) {
            $this->add(array(
                'type' => 'Hidden',
                'name' => $name,
                'value' => $value,
            ));
        }
    }
    
    protected function addBaseFieldset()
    {
        if (null === $this->baseFieldset) {
            return;
        }
        
        $fs = $this->baseFieldset;
        if (!is_array($fs)) {
            $fs = array(
                'type' => $fs,
            );
        }
        if (!isset($fs['options']['use_as_base_fieldset'])) {
            $fs['options']['use_as_base_fieldset'] = true;
        }
        $this->add($fs);
    }
    
    protected function addButtonsFieldset()
    {
        $this->add(array(
            'type' => 'DefaultButtonsFieldset'
        ));
    }
    
    protected function addHydratorStrategies($hydrator)
    { }
    
    public function addClass($spec) {
        $class = array();
        if ($this->hasAttribute('class')) {
            $class = $this->getAttribute('class');
        }
        if (!is_array($class)) {
            $class = explode( ' ', $class);
        }
        if (!in_array($spec, $class)) {
            $class[] = $spec;
        }
        $this->setAttribute('class', implode(' ',$class));
        return $this;
    }
    
    public function setValidate() {
        return $this->addClass('validate');
    }
    
}