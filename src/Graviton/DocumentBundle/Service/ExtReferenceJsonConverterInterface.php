<?php
/**
 * ExtReferenceJsonConverterInterface class file
 */
namespace Graviton\DocumentBundle\Service;

/**
 * Extref converter interface
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface ExtReferenceJsonConverterInterface
{

    /**
     * Convert $refs to URLs in input data
     *
     * @param mixed $data
     * @param array $fields
     * @return array
     */
    public function convert($data, $fields);
}