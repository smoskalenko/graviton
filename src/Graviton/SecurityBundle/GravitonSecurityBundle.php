<?php

namespace Graviton\SecurityBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Graviton\SecurityBundle\DependencyInjection\AuthenticationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonSecurityBundle
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class GravitonSecurityBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return array();
    }

    /**
     * Find authentication strategies tagged as 'graviton.security.authentication.strategy'
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AuthenticationPass());
    }
}
