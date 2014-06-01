<?php

namespace Graviton\RestBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * FilterResponseListener for setting up CORS headers.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class CorsResponseListener
{
    /**
     * add a rel=self Link header to the response
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

	$response->headers->set('Access-Control-Allow-Origin', '*');

        $event->setResponse($response);
    }
}
