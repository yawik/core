<?php

namespace Core\Form\View\Helper;

use Zend\Form\View\Helper\FormRow as ZendFormRow;
use Zend\Form\ElementInterface;

class FormRow extends ZendFormRow
{
    
    protected $layout;
    protected $shouldWrap = true;
    protected $labelSpanWidth = 3;
    
    
    public function setShouldWrap($flag)
    {
        $this->shouldWrap = (bool) $flag;
        return $this;
    }
    /**
     * Utility form helper that renders a label (if it exists), an element and errors
     *
     * @param ElementInterface $element
     * @return string
     * @throws \Zend\Form\Exception\DomainException
     */
    public function render(ElementInterface $element)
    {
        $escapeHtmlHelper    = $this->getEscapeHtmlHelper();
        $labelHelper         = $this->getLabelHelper();
        $elementHelper       = $this->getElementHelper();
        $elementErrorsHelper = $this->getElementErrorsHelper();
    
        $label           = $element->getLabel();
        $inputErrorClass = $this->getInputErrorClass();
        $elementErrors   = $elementErrorsHelper->render($element);
    
        // Does this element have errors ?
        if (!empty($elementErrors) && !empty($inputErrorClass)) {
            $classAttributes = ($element->hasAttribute('class') ? $element->getAttribute('class') . ' ' : '');
            $classAttributes = $classAttributes . $inputErrorClass;
    
            $element->setAttribute('class', $classAttributes);
        }
    
        if (!$element->hasAttribute('id')) {
            $elementId = preg_replace(
                array('~[^A-Za-z0-9_-]~', '~--+~', '~^-|-$~'),
                array('-'             , '-',     ''),
                $element->getName()
            );
            $element->setAttribute('id', $elementId);
        } else {
            $elementId = $element->getAttribute('id');
        }
        if (! $element instanceOf \Zend\Form\Element\Button) {
            $inputSize = $element->getOption('inputSize');
            if (!$inputSize || 'block' == $inputSize) {
                $inputSize = 'block-level';
            }
            $element->setAttribute('class', $element->getAttribute('class') . ' input-' . $inputSize);
        }
        $elementString = $elementHelper->render($element);
        if ($desc = $element->getOption('description', false)) {
            $elementString .= sprintf(
                '<div class="description help-block">%s</div>', $desc
            );
        }
        if (!$element instanceOf \Zend\Form\Element\Hidden
            && !$element instanceOf \Zend\Form\Element\Button
        ) {
            $elementString .= sprintf(
                '<div id="%s-errors">%s</div>',
                $elementId, $elementErrors
            );
        }
        
        
    
        if (isset($label) && '' !== $label && !$element instanceOf \Zend\Form\Element\Button) {
            // Translate the label
            if (null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate(
                    $label, $this->getTranslatorTextDomain()
                );
            }
    
            $label = $escapeHtmlHelper($label);
            $labelAttributes = $element->getLabelAttributes();
    
            if (empty($labelAttributes)) {
                $labelAttributes = $this->labelAttributes;
            }
    
            // Multicheckbox elements have to be handled differently as the HTML standard does not allow nested
            // labels. The semantic way is to group them inside a fieldset
            $type = $element->getAttribute('type');
            if ($type === 'multi_checkbox' || $type === 'radio') {
                $markup = sprintf(
                    '<fieldset><legend>%s</legend>%s</fieldset>',
                    $label,
                    $elementString);
            } else {
                
                $labelAttributes = $element->getLabelAttributes();
                if (!isset($labelAttributes['for'])) {
                    $labelAttributes['for'] = $elementId;
                }
                if ('form-horizontal' == $this->layout) {
                    if (!isset($labelAttributes['class'])) {
                        $labelAttributes['class'] = '';
                    }
                    $labelAttributes['class'] .= ' control-label';
                }
                $element->setLabelAttributes($labelAttributes);
                
                
                    $labelOpen = '';
                    $labelClose = '';
                    $label = $labelHelper($element);
                    $labelWidth = $element->getOption('labelWidth');
                    if (!$labelWidth) {
                        $labelWidth = $this->labelSpanWidth;
                    }
                    if ($this->shouldWrap) {
                        $spanWidth = 12 - $labelWidth;
                        $elementString = sprintf(
                            '<div class="span%d%s" id="' . $elementId . '-span">%s</div>',
                            $spanWidth, $elementErrors ? " $inputErrorClass" : '', $elementString
                        );
                        $label = sprintf(
                            '<div class="span%d text-right">%s</div>',
                            $labelWidth, $label
                        );
                        
                    } 
                    $markup = $label . $elementString;
                    
                     
                
            }
    
            
        } else {
            if ($this->shouldWrap 
                && !$element instanceOf \Zend\Form\Element\Hidden
                && !$element instanceOF \Zend\Form\Element\Button) {
                $elementString = sprintf(
                    '<div class="span12">%s</div>', $elementString
                );
            } 
            
                $markup = $elementString;
            
        }
        if ($this->shouldWrap 
            && !$element instanceOf \Zend\Form\Element\Hidden
            && !$element instanceOf \Zend\Form\Element\Button) {
            $markup = sprintf('<div class="controls controls-row row-fluid">%s</div>', $markup);
        }
    
        return $markup;
    }
    
    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param null|ElementInterface $element
     * @param null|string           $labelPosition
     * @param bool                  $renderErrors
     * @return string|FormRow
     */
    public function __invoke(ElementInterface $element = null, $labelPosition = null, $renderErrors = null, $layout=null)
    {
        if (!$element) {
            return $this;
        }
    
        if ($labelPosition !== null) {
            $this->setLabelPosition($labelPosition);
        } else {
            $this->setLabelPosition(self::LABEL_PREPEND);
        }
    
        if ($renderErrors !== null){
            $this->setRenderErrors($renderErrors);
        }
        
        if (null !== $layout) {
            $this->layout = $layout;
        }
    
        return $this->render($element);
    }
    
}
