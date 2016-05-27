<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author    weitz@cross-solution.de
 */

namespace Core\Listener;

use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Core\Listener\Events\NotificationEvent;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManager;
use Zend\View\Model\JsonModel;

/**
 *
 * @todo [MG] This must be refactored! It's a violation of the encapsulation principle.
 *       I mean... an event manager which is a listener for itself? What a mess!
 */
class NotificationListener extends EventManager implements SharedListenerAggregateInterface
{

    /**
     * @var array
     */
    protected $notifications = array();
    
    /**
     * @var bool
     */
    protected $hasRunned = true;

    /**
     * @see \Zend\EventManager\SharedListenerAggregateInterface::attachShared()
     */
    public function attachShared(SharedEventManagerInterface $events)
    {
        $events->attach('*', NotificationEvent::EVENT_NOTIFICATION_ADD, array($this,'add'), 1);
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH, array($this,'renderJSON'), -240);
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH, array($this,'renderHTML'), -250);
        // Sometimes the Dispatch-Event is not reached, for instance with a route-direct
        // but also for Events, that are happening after the Dispatch
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_FINISH, array($this,'renderHTML'), -250);
        return $this;
    }

    /**
     * @see \Zend\EventManager\SharedListenerAggregateInterface::detachShared()
     */
    public function detachShared(SharedEventManagerInterface $events)
    {
        return $this;
    }

    /**
     * @param NotificationEvent $event
     * @return \Core\Listener\NotificationListener
     */
    public function add(NotificationEvent $event)
    {
        $notification = $event->getNotification();
        $this->notifications[] = $notification;
        $this->hasRunned = false;
        return $this;
    }

    /**
     * Special handling json
     * @param MvcEvent $event
     */
    public function renderJSON(MvcEvent $event)
    {
        if (!$this->hasRunned) {
            $valueToPlainStati = array(1 => 'error', 2 => 'error', 3 => 'error', 4 => 'error', 5 => 'success', 6 => 'info', 7 => 'info');
            $viewModel = $event->getViewModel();
            if ($viewModel instanceof JsonModel) {
                if (!empty($this->notifications)) {
                    $jsonNotifications = $viewModel->getVariable('notifications', array());
                    foreach ($this->notifications as $notification) {
                        $status = 'info';
                        if (array_key_exists($notification->getPriority(), $valueToPlainStati)) {
                            $status = $valueToPlainStati[$notification->getPriority()];
                        }
                        $jsonNotifications[] = array(
                            'text' => $notification->getNotification(),
                            'status' => $status
                        );
                    }
                    $viewModel->setVariable('notifications', $jsonNotifications);
                }
                $this->hasRunned = true;
            }
        }
        return;
    }

    /**
     * @return \Core\Listener\NotificationListener
     */
    public function reset()
    {
        $this->hasRunned = false;
        return $this;
    }

    /**
     * @param MvcEvent $event
     * @return \Core\Listener\NotificationListener
     */
    public function renderHTML(MvcEvent $event)
    {
        if (!$this->hasRunned) {
            $nEvent = new NotificationEvent();
            $nEvent->setNotifications($this->notifications);
            $this->trigger(NotificationEvent::EVENT_NOTIFICATION_HTML, $nEvent);
            $this->notifications = array();
            $this->hasRunned = true;
        }
        return $this;
    }
}
