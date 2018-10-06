<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * @see https://symfony.vojtechvondra.cz/en/2014-08-28-saving-the-last-matched-route
 */
class LastRouteListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        // Do not save subrequests
        if ($event->getRequestType() !== HttpKernel::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        $routeName = $request->get('_route');

        // do not save api calls
        if (true === (bool) preg_match('/^api\./', $routeName)) {
            return;
        }

        $routeParams = $request->get('_route_params');
        if ($routeName[0] == '_') {
            return;
        }
        $routeData = ['name' => $routeName, 'params' => $routeParams];

        // Do not save same matched route twice
        $thisRoute = $session->get('this_route', []);
        if ($thisRoute == $routeData) {
            return;
        }
        $session->set('last_route', $thisRoute);
        $session->set('this_route', $routeData);
    }
}
