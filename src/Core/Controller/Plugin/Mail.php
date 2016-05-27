<?php

namespace Core\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\PluginInterface;
use Zend\Stdlib\DispatchableInterface as Dispatchable;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\AddressList;
use Zend\EventManager\Event;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\ArrayUtils;
use Zend\Mvc\Controller\PluginManager as ControllerManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class mail extends Message implements PluginInterface
{
    
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceManager;
    
    /**
     * @var Dispatchable
     */
    protected $controller;
    
    /**
     * @var array
     */
    protected $param;
    
    /**
     * @var array
     */
    protected $config;

    /**
     * @param ServiceLocatorInterface $serviceManager
     */
    public function __construct(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    /**
     * Set the current controller instance
     *
     * @param  Dispatchable $controller
     * @return void
     */
    public function setController(Dispatchable $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get the current controller instance
     *
     * @return null|Dispatchable
     */
    public function getController()
    {
        return $this->controller;
    }
    
    public function __invoke(array $param = array())
    {
        $this->param = $param;
        $this->config = array();
        return $this;
    }
    
    protected function stringFromMailHeader($header)
    {
        $erg = '';
        if ($header instanceof AddressList) {
            $A = ArrayUtils::iteratorToArray($header);
            $AA = array();
            while (!empty($A)) {
                $AA[] = array_shift($A)->toString();
            }
            $erg = implode(',', $AA);
        }
        return $erg;
    }
    
    public function __toString()
    {
        $template = $this->getTemplate();
        $to = $this->stringFromMailHeader($this->getTo());
        $cc = $this->stringFromMailHeader($this->getCc());
        $bcc = $this->stringFromMailHeader($this->getBcc());
        $from = $this->stringFromMailHeader($this->getFrom());
        $replyTo = $this->stringFromMailHeader($this->getReplyTo());
        
        return str_pad($template, 30)
                . 'to: ' . str_pad($to, 50)
                . 'cc: ' . str_pad($cc, 50)
                . 'bcc: ' . str_pad($bcc, 50)
                . 'from: ' . str_pad($from, 50)
                . 'replyTo: ' . str_pad($replyTo, 50)
                //. str_pad(implode(',', ArrayUtils::iteratorToArray($this->getSender())),50)
                . 'subject: ' . str_pad($this->getSubject(), 50);
    }
    
    public function template($template)
    {
        $controller =  get_class($this->controller);
        $services = $this->serviceManager;
        
        $event = new Event();
        $eventManager = $services->get('EventManager');
        $eventManager->setIdentifiers('Mail');
        $p = new Parameters(array('mail' => $this, 'template' => $template));
        $event->setParams($p);
        $eventManager->trigger('template.pre', $event);
        
        // get all loaded modules
        $moduleManager = $services->get('ModuleManager');
        $loadedModules = $moduleManager->getModules();
        //get_called_class
        $controllerIdentifier = strtolower(substr($controller, 0, strpos($controller, '\\')));
        $viewResolver = $this->serviceManager->get('ViewResolver');
                
        $templateHalf = 'mail/' . $template;
        $resource = $viewResolver->resolve($templateHalf);
        
        if (empty($resource)) {
            $templateFull = $controllerIdentifier . '/mail/' . $template;
            $resource = $viewResolver->resolve($templateFull);
        }
        
        $__vars = $this->param;
        if (array_key_exists('this', $__vars)) {
            unset($__vars['this']);
        }
        extract($__vars);
        unset($__vars); // remove $__vars from local scope
        
        if ($resource) {
            try {
                ob_start();
                include $resource;
                $content = ob_get_clean();
                $this->setBody($content);
            } catch (\Exception $ex) {
                ob_end_clean();
                throw $ex;
            }
            $__vars = get_defined_vars();
            foreach ($this->param as $key => $value) {
                if (isset($__vars[$key])) {
                    unset($__vars[$key]);
                }
            }
            unset($__vars['content'],$__vars['controllerIdentifier'],$__vars['controller'],$__vars['resource'],$__vars['template'],$__vars['viewResolver']);
            $this->config = $__vars;
        }
    }
    
    protected function getTemplate()
    {
        $template = null;
        if (isset($this->config['templateFull'])) {
            $template = $this->config['templateFull'];
        } elseif (isset($this->config['templateHalf'])) {
            $template = $this->config['templateHalf'];
        } else {
              throw new \InvalidArgumentException('Not template provided for Mail.');
        }
        return $template;
    }
    
    public function informationComplete()
    {
        $log = $this->serviceManager->get('Log/Core/Mail');
        $template = $this->getTemplate();
        if (isset($this->config['from'])) {
            $from = $this->config['from'];
        } else {
            $log->err('A from email address must be provided (Variable $from) in Template: ' . $template);
              throw new \InvalidArgumentException('A from email address must be provided (Variable $from) in Template: ' . $template);
        }
        if (isset($this->config['fromName'])) {
            $fromName = $this->config['fromName'];
        } else {
            $log->err('A from name must be provided (Variable $fromName) in Template: ' . $template);
              throw new \InvalidArgumentException('A from name must be provided (Variable $fromName) in Template: ' . $template);
        }
        if (isset($this->config['subject'])) {
            $subject = $this->config['subject'];
        } else {
            $log->err('A subject must be provided (Variable $subject) in Template: ' . $template);
              throw new \InvalidArgumentException('A subject must be provided (Variable $subject) in Template: ' . $template);
        }
        $this->setFrom($from, $fromName);
        $this->setSubject($subject);
        return $this;
    }
    
    
    public function send()
    {
        $log = $this->serviceManager->get('Log/Core/Mail');
        $this->getHeaders()->addHeaderLine('X-Mailer', 'php/YAWIK');

        $this->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');

        $transport = new Sendmail();
        $erg = false;
        try {
            $transport->send($this);
            $erg = true;
            $log->info($this);
        } catch (\Exception $e) {
            $log->err('Mail failure ' . $e->getMessage());
            //$this->serviceManager->get('Core/Log')->warn('Mail failure ' . $e->getMessage());
        }
        //}
        return $erg;
    }
    
    /**
     * @param ControllerManager $controllerManager
     * @return mail
     */
    public static function factory(ControllerManager $controllerManager)
    {
        return new static($controllerManager->getServiceLocator());
    }
}
