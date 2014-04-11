<?php

namespace Elao\Bundle\BrowserDetectorBundle\EventListener;

use Elao\BrowserDetector\BrowserDetector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Router;

class RequestListener
{
    /**
     * Listener manager
     * @var BrowserDetector
     */
    private $browserDetector;
    private $router;
    private $redirect;

    /**
     * Constructor
     * @param BrowserDetector $browserDetector The listener manager service
     */
    public function __construct(BrowserDetector $browserDetector, Router $router, $redirect)
    {
        $this->browserDetector = $browserDetector;
        $this->router = $router;
        $this->redirect = $redirect;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $this->browserDetector->setRequest($event->getRequest());

        if ($this->redirect
            && $this->browserDetector->isIncompatible()
            && $event->getRequest()->get('_route') !== $this->redirect) {
            $redirect = $this->router->generate($this->redirect);
            $response = new RedirectResponse($redirect);
            $event->setResponse($response);
        }
    }
}
