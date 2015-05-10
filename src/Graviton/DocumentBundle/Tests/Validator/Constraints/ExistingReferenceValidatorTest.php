<?php
/**
 * validate existing extref constraint validator
 */

namespace Graviton\DocumentBundle\Test\Validator\Constraints;

use Graviton\DocumentBundle\Validator\Constraints\ExistingReference;
use Graviton\DocumentBundle\Validator\Constraints\ExistingReferenceValidator;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExistingReferenceValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validateData
     *
     * @param string  $value value to check
     * @param boolean $valid is the value valid or not
     *
     * @return void
     */
    public function testValidate($value, $valid)
    {
        $contextDouble = $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $dmDouble = $this
            ->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $constraint = new ExistingReference;

        if ($valid) {
            $contextDouble
                ->expects($this->never())
                ->method('buildViolation');
        } else {
            $this->markTestIncomplete();
            $contextDouble
                ->expects($this->once())
                ->method('buildViolation')
                ->with($constraint->message);
        }

        $sut = new ExistingReferenceValidator($dmDouble);

        $sut->initialize($contextDouble);
        $sut->validate($value, $constraint);
    }

    /**
     * @return array
     */
    public function validateData()
    {
        return [
            ['http://localhost/core/app/tablet', true],
            ['http://localhost/core/app/windows', false],
        ];
    }
}
