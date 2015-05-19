<?php
/**
 * validate AbstractCustomerController
 */

namespace Graviton\PersonBundle\Tests\Controller;

use Graviton\PersonBundle\Controller\AbstractCustomerController;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AbstractCustomerControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testInstanciateController()
    {
        $diffRepoDouble = $this->getMockBuilder('Graviton\PersonBundle\Repository\CustomerDiffRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $stub = $this->getMockForAbstractClass(
            'Graviton\PersonBundle\Controller\AbstractCustomerController',
            [
                $diffRepoDouble
            ]
        );

        $this->assertInstanceOf('Graviton\RestBundle\Controller\RestController', $stub);
    }

    /**
     * @return void
     */
    public function testPostAction()
    {
        $diffRepoDouble = $this->getMockBuilder('Graviton\PersonBundle\Repository\CustomerDiffRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $requestDouble = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $stub = $this->getMockForAbstractClass(
            'Graviton\PersonBundle\Controller\AbstractCustomerController',
            [
                $diffRepoDouble
            ]
        );
        $this->markTestIncomplete();

        $stub->postAction($requestDouble);
    }
}
