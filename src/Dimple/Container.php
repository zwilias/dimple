<?php


namespace Dimple;


use Pimple\ServiceProviderInterface;

class Container extends \Pimple\Container
{
    /** @var \Pimple\Container */
    protected $pimpleContainer;

    /** @var string[] */
    protected $loadedNamespaces = array();

    /** @var ServiceProviderInterface[] */
    protected $namespaceProviders = array();

    public function __construct(array $values = array())
    {
        $this->pimpleContainer = new \Pimple\Container($values);
    }

    public function offsetSet($id, $value)
    {
        $this->pimpleContainer->offsetSet($id, $value);
    }

    public function offsetGet($id)
    {
        return $this->pimpleContainer->offsetGet(
            $this->ensureNamespacesLoaded($id)
        );
    }

    public function offsetExists($id)
    {
        return $this->pimpleContainer->offsetExists(
            $this->ensureNamespacesLoaded($id)
        );
    }

    public function offsetUnset($id)
    {
        $this->pimpleContainer->offsetUnset($id);
    }

    public function factory($callable)
    {
        return $this->pimpleContainer->factory($callable);
    }

    public function protect($callable)
    {
        return $this->pimpleContainer->protect($callable);
    }

    public function raw($id)
    {
        return $this->pimpleContainer->raw(
            $this->ensureNamespacesLoaded($id)
        );
    }

    public function extend($id, $callable)
    {
        $this->ensureNamespacesLoaded($id);

        return $this->pimpleContainer->extend(
            $this->ensureNamespacesLoaded($id), $callable
        );
    }

    public function keys()
    {
        return $this->pimpleContainer->keys();
    }

    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        return $this->pimpleContainer->register($provider, $values);
    }

    public function registerServiceProviderProvider(ServiceProviderProviderInterface $providerProvider)
    {
        foreach ($providerProvider->provideServiceProviders($this) as $nameSpace => $serviceProvider) {
            if (isset($this->namespaceProviders[$nameSpace])) {
                throw new \RuntimeException('Can\'t redefine serviceProvider for namespace ' . $nameSpace);
            }

            if (!is_string($nameSpace)) {
                throw new \RuntimeException('Namespace must be a string');
            }

            if (! $serviceProvider instanceof ServiceProviderInterface) {
                throw new \RuntimeException('ServiceProviderProvider must provide map of ServiceProviders');
            }

            $this->namespaceProviders[$nameSpace] = $serviceProvider;
        }
    }

    protected function loadNamespace($name)
    {
        if (in_array($name, $this->loadedNamespaces)) {
            return;
        }

        if (! isset($this->namespaceProviders[$name])) {
            throw new \InvalidArgumentException('No providerProvider registered for namespace ' . $name);
        }

        $this->namespaceProviders[$name]->register($this);
        $this->loadedNamespaces[] = $name;
    }

    protected function ensureNamespacesLoaded($id)
    {
        if (($split = strpos($id, '::')) !== false) {
            $this->loadNameSpace(substr($id, 0, $split));
        }

        return $id;
    }
}
