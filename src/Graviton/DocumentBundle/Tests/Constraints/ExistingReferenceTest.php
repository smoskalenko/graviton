<?php
/**
 * validate existing extref constraint
 */

namespace Graviton\DocumentBundle\Test\Constraints;

use Graviton\DocumentBundle\Constraints\ExistingReference;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExistingReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testMessage()
    {
        $sut = new ExistingReference;

        $this->assertEquals('The reference "%" does not exist. It must point to an existing endpoint.', $sut->message);
    }
}
