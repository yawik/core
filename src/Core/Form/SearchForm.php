<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace Core\Form;

use Traversable;
use Zend\Form\Exception;
use Zend\Form\Form as ZfForm;
use Zend\Json\Json;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\PriorityList;

/**
 * ${CARET}
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test 
 */
class SearchForm extends ZfForm
{
    protected $attributes = [
        'class'          => 'form-inline search-form',
        'data-handle-by' => 'script',
        'method'         => 'get',
    ];

    /**
     *
     *
     * @var \Zend\Stdlib\PriorityList
     */
    protected $buttonsIterator;

    protected $multiValueFields = [];

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
        $this->buttonsIterator = new PriorityList();
        $this->buttonsIterator->isLIFO(false);
    }

    public function setButtonElement($name)
    {
        return $this->setOption('button_element', $name);
    }

    public function getButtonElement()
    {
        return $this->getOption('button_element');
    }

    /**
     * Sets the column map.
     *
     * @param array $map
     *
     * @see \Core\Form\View\Helper\SearchForm
     * @return self
     */
    public function setColumnMap($map)
    {
        $this->setOption('column_map', $map);

        return $this;
    }

    /**
     * Gets the column map.
     *
     * Generates the column map from the element options,
     * if none is set.
     *
     * @return array
     */
    public function getColumnMap()
    {
        $map = $this->getOption('column_map');

        if (null === $map) {
            $map = [];
            foreach ($this as $element) {
                $col = $element->getOption('span');
                if (null !== $col) {
                    $map[$element->getName()] = $col;
                }
            }

            $this->setOption('column_map', $map);
        }

        return $map;
    }

    public function setMultiValueFields(array $fields)
    {
        $multiValues = [];

        foreach ($fields as $name => $separator) {
            if (is_numeric($name)) {
                $name = $separator;
                $separator = ',';
            }

            $multiValues[$name] = $separator;
        }

        $this->multiValuesFields = $multiValues;
        $this->setAttribute('data-multivalues', Json::encode($multiValues));

        return $this;
    }

    public function setData($data)
    {
        foreach ($this->multiValueFields as $name => $separator) {
            if (array_key_exists($name, $data)) {
                $data[$name] = explode($separator, $data[$name]);
            }
        }

        return parent::setData($data); // TODO: Change the autogenerated stub
    }


    public function init()
    {
        $this->addTextElement(
            $this->getOption('text_name') ?: /*@translate*/ 'q',
            $this->getOption('text_label') ?: /*@translate*/ 'Search',
            $this->getOption('text_placeholder') ?: /*@translate*/ 'Search query',
            $this->getOption('text_span') ?: 12,
            50,
            true
        );

        $this->addButton(/*@translate*/ 'Search', -1000, 'submit');
        $this->addButton(/*@translate*/ 'Clear', -1001, 'reset');

        $this->addElements();
    }

    protected function addElements()
    {

    }

    public function addTextElement($name, $label, $placeholder, $span = 12, $priority = 0, $isButtonElement = false)
    {
        $this->add(
            [
                'type' => 'Text',
                'options' => [
                    'label' => $label,
                    'span' => $span,
                ],
                'attributes' => [
                    'placeholder' => $placeholder,
                    'class' => 'form-control',
                ],
            ],
            [
                'name' => $name,
                'priority' => $priority,
            ]
        );

        if ($isButtonElement) {
            $this->setOption('button_element', $name);
        }
    }

    public function getButtons()
    {
        return $this->buttonsIterator;
    }

    public function addButton($name, $priority = 0, $type = 'button')
    {
        if (is_array($name)) {
            $label = $name[1];
            $name = $name[0];
        } else {
            $label = $name;
            $name = strtolower($name);
        }

        $factory = $this->getFormFactory();

        $button = $factory->create(
            [
                'type' => 'Button',
                'options' => [
                    'label' => $label,
                ],
                'attributes' => [
                    'class' => 'btn btn-' . ('submit' == $type ? 'primary' : 'default'),
                    'type'  => $type,
                ],
            ]
        );
        $button->setName($name);

        $this->buttonsIterator->insert($name, $button, $priority);

        return $this;
    }

    /**
     * Sets the initial search params.
     *
     * That means, the values for the elements
     * which should be set, if the form resets.
     *
     * @param array|\Traversable $params
     *
     * @return self
     */
    public function setSearchParams($params)
    {
        if ($params instanceOf \Traversable) {
            $params = ArrayUtils::iteratorToArray($params);
        }

        $params = Json::encode($params);
        $this->setAttribute('data-search-params', $params);

        return $this;
    }

}