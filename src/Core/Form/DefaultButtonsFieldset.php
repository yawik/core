<?php

namespace Core\Form;


class DefaultButtonsFieldset extends ButtonsFieldset
{
    public function init()
    {
        $this->setName('buttons');
        //$this->setLabel('Actions');
        $this->add(array(
            'type' => 'Button',
            'name' => 'submit',
            'options' => array(
                'label' => /*@translate*/ 'Save',
            ),
            'attributes' => array(
                'id' => 'submit',
                'type' => 'submit',
                'value' => 'Save',
                'class' => 'cam-btn-save'
            ),
        ));
        
        $this->add(array(
            'type' => 'Button',
            'name' => 'cancel',
            'options' => array(
                'label' => /*@translate*/ 'Cancel',
            ),
            'attributes' => array(
                'id' => 'cancel',
                'type' => 'reset',
                'value' => 'Cancel',
                'class' => 'cam-btn-reset'
            ),
        ));
    }
}