<?php


namespace Dimple;


use Pimple\ServiceProviderInterface;

interface ServiceProviderProviderInterface
{
    /**
     * @param Container $container
     * @return ServiceProviderInterface[]
     */
    function provideServiceProviders(Container $container);
}
