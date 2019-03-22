<?php

namespace StExceptionsHandler\Service;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\Application;
use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response as HttpResponse;

class StExceptionsHandlerService
{
    /**
     * @var int
     */
    protected $eventPriority = 120;

    /**
     * @var StException
     */
    protected $exception;


    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events): void
    {
        $events->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            function (MvcEvent $event): void {
                // this closure is just because of the lack of consistent callable usage in PHP
                $this->onError($event);
            },
            $this->eventPriority
        );
        $events->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            [$this, 'onError'],
            $this->eventPriority
        );
    }

    /**
     * The global StExceptions Handler
     * All the exceptions occurred in our app will be handled bellow
     *
     * @param MvcEvent $event
     */
    public function onError(MvcEvent $event): void
    {
//        dump('onError handler');

        // Do nothing if no error in our life circle
        $error = $event->getError();
        if (empty($error))
            return;

        // Do nothing if the result is a response object
        $result = $event->getResult();
        if ($result instanceof Response)
            return;

        $this->exception = $event->getParam('exception');

        switch ($error) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
            case Application::ERROR_ROUTER_NO_MATCH:
                //Specifically handle these with 404
                $this->exception = new NotFoundHttpException();
                $this->render($event);
                $event->stopPropagation();

                return;
            case Application::ERROR_EXCEPTION:
            default:
                break;
        }

        /** @var HttpResponse $response */
        $response = $event->getResponse();
        if (!$response) {
            $response = new HttpResponse();
            $event->setResponse($response);
        }

        if ($event->getName() == MvcEvent::EVENT_DISPATCH_ERROR) {
            $controller = $event->getTarget();
            $eventResponse = $controller->getEventManager()->triggerEvent(
            //It must be "dispatch.error"
                $controller->getEvent()
            );
        }

        //no controller handler, lets run our own
        if (empty($eventResponse) || !$eventResponse->contains('StExceptionsHandled'))
            $this->render($event);

        $event->stopPropagation();
        return;
    }

    /**
     * Report or log an exception.
     *
     * @return void
     * @throws \Exception
     */
    public function report()
    {
//        if ($this->shouldntReport($e))
//            return;

//        if (method_exists($e, 'report')) {
//            return $e->report();
//        }
//
//        //log the error bellow
//        return;
    }

    /**
     * Render an exception into a response.
     *
     * @param  \Exception $e
     */
    public function render(MvcEvent $event): void
    {
        if (method_exists($this->exception, 'render')) {
            $this->exception->render($event);

            return;
        }
    }
}
