<?php
/**
 * validate AbstractCustomerController
 *
 * This contains integration tests that aim at testing the AbstractCustomerController. It does
 * so using the graviton/test-services-bundle that have been incepted for this purpose.
 */

namespace Graviton\PersonBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CustomerControllerTest extends RestTestCase
{
    /**
     * @return void
     */
    public function testPostAction()
    {
        $client = static::createRestClient();

        $testCustomer = new \stdClass;
        $testCustomer->name = 'Ford Prefect';

        $client->post('/person/customer', $testCustomer);

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }
}
