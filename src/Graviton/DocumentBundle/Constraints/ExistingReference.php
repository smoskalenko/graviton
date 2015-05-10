<?php
/**
 * constraint for required references
 */

namespace Graviton\DocumentBundle\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExistingReference extends Constraint
{
    public $message = 'The reference "%" does not exist. It must point to an existing endpoint.';
}
