<?php
/**
 * abstract customer controller
 *
 * Implements async reading and writing for static data.
 *
 * We have heaps of 'unchangeable' data coming from complex enterprisy systems that have some data we need
 * want to let our users change nevertheless. This controller implements a diff-layer to that data and
 * allows us to store differences to the mainframe locally.
 *
 * We do this by adding an additional collection to mongodb containing an overlay of diff records. The idea
 * is that there is also tooling that enables us to write back the contents of this diff layer into the
 * enterprisy data container at a later stage following the varying complex scenarios needed to get that data
 * back into the mainframe.
 */

namespace Graviton\PersonBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use Graviton\PersonBundle\Repository\CustomerDiffRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
abstract class AbstractCustomerController extends RestController
{
    /**
     * @var CustomerDiffRepository
     */
    private $diffRepo;

    /**
     * @param CustomerDiffRepository $diffRepo repo containing customer diffs
     */
    public function __construct(CustomerDiffRepository $diffRepo)
    {
        $this->diffRepo = $diffRepo;
    }

    /**
     * save data to diff collection on post
     *
     * @param Request $request client request
     *
     * @return Response $response
     */
    public function postAction(Request $request)
    {
        // grab request and check data against original schema
        $response = $this->getResponse();
        $record = $this->deserialize(
            $request->getContent(),
            'GravitonDyn\CustomerBundle\Document\Customer'
        );

        // create diff object
        $documentClass = $this->diffRepo->getClassName();
        $diff = new $documentClass;
        $diff->setOriginal(null);
        $diff->setNew($record);

        // save diff data
        $manager = $this->diffRepo->getDocumentManager();
        $manager->persist($diff);
        $manager->flush();
        $diff = $this->diffRepo->find($diff->getId());

        // Set status code and content
        $response->setStatusCode(Response::HTTP_CREATED);
        $response->setContent($this->serialize($diff->getNew()));
 
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);

        if ($routeType == 'post') {
            $routeName = substr($routeName, 0, -4) . 'get';
        }

        $response->headers->set(
            'Location',
            $this->getRouter()->generate($routeName, array('id' => $record->getId()))
        );

        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            array('response' => $response->getContent()),
            $response
        );
    }
}
