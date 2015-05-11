<?php
/**
 * validate if references exist or not
 */

namespace Graviton\DocumentBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExistingReferenceValidator extends ConstraintValidator
{
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @param DocumentManager $dm doctrine odm dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @param mixed      $value      value to check
     * @param Constraint $constraint constraint to match
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
    }
}
