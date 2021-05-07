<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_dependencyInjection_Container
{
    /** @var \Closure[]|string[]|object[] */
    private $serviceFactories = [];

    /** @var array */
    private $services = [];

    /**
     * @throws Exception
     *
     * @param string $path
     */
    public function readConfigiration($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception('Configuration file not found: '.$filePath);
        }
        foreach (require($filePath) as $name => $factory) {
            $this->set($name, $factory);
        }
    }

    /**
     * @param string $name
     * @param \Closure|string|object $factory
     */
    public function set($name, $factory)
    {
        $services = $this->splitServicesName($name);
        foreach($services as $k=>$v) {
            $serviceName = $this->fixServiceName($v);
            $this->serviceFactories[$serviceName] = $factory;
            unset($this->services[$serviceName]);
        }
    }

    /**
     * @param string $name
     * @param \Closure|string $factory
     * @param string $facadeName
     */
    public function setAndCreateFacade($name, $factory, $facadeName)
    {
        $services = $this->splitServicesName($name);
        foreach($services as $k=>$v) {
            $serviceName = $this->fixServiceName($v);
            $this->set($serviceName, $factory);
            if ($k==0) {
                $service = $this->get($serviceName);
                $this->createFacade($facadeName, $service);
            }
        }
    }

    /**
     * @throws Exception
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        $fixedName = $this->fixServiceName($name);
        if (!$this->has($fixedName)) {
            $args = func_get_args();
            $className = array_shift($args);
            $obj = $this->createClass($className, $args);
            if (!$obj) {
                throw new Exception('Service not found: '.$name);
            }
            return $obj;
        }

        if (!isset($this->services[$fixedName])) {
            $this->services[$fixedName] = $this->createService($fixedName);
        }

        if (!$this->services[$fixedName]) {
            throw new Exception('Service not found: '.$name);
        }

        return $this->services[$fixedName];
    }

    /**
     * @param string $serviceId
     * @return bool
     */
    public function has($name)
    {
        return isset($this->serviceFactories[$this->fixServiceName($name)]);
    }

    /**
     * @param  string $name
     * @return string
     */
    private function fixServiceName($name)
    {
        return str_replace('.', '_', strtolower($name));
    }

    /**
     * @param  string $name
     * @return array
     */
    private function splitServicesName($name)
    {
        return is_array($name) ? $name : explode(',', $name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function createService($name)
    {
        $factory = $this->serviceFactories[$name];
        if (method_exists($factory, '__invoke')) {
            return $factory($this);
        } else if (is_object($factory)) {
            return $factory;
        }

        return $this->createClass($factory);
    }

    /**
     * @param  string $className
     * @param  array $args
     * @return mixed
     */
    private function createClass($className, $args=null)
    {
        $className = str_replace('.', '_', $className);

        $reflectionClass = new \ReflectionClass($className);
        if (!$reflectionClass->isInstantiable()) {
            throw new \Exception(sprintf('%s: class %s is not instantiable', __METHOD__, $className));
        }

        $constructor = $reflectionClass->getConstructor();
        if (!$constructor) {
            return new $className();
        }
        $construcParameters = $constructor->getParameters();

        $rewriteArgs = array();
        $numArgs = is_array($args) ? count($args) : 0;
        foreach ($construcParameters as $key => $param) {
            if ($param->isDefaultValueAvailable()) {
                $rewriteArgs[$key] = $param->getDefaultValue();
            }
            if ($key < $numArgs) {
                if ($param->isPassedByReference()) {
                    $rewriteArgs[$key] = &$args[$key];
                } else {
                    $rewriteArgs[$key] = $args[$key];
                }
            } else {
                $paramClass = $param->getClass();
                $keyName = $paramClass ? $paramClass->name : $param->name;
                if ($this->has($keyName)) {
                    $rewriteArgs[$key] = $this->has($keyName) ? $this->get($keyName) : null;
                } else if (!$param->isDefaultValueAvailable()) {
                    throw new pinax_dependencyInjection_BindingResolutionException(sprintf('%s: class %s can not inject constructor param: %s', __METHOD__, $className, $keyName));
                }
            }
        }

        return $reflectionClass->newInstanceArgs($rewriteArgs);
    }

    /**
     * @param string $facadeName
     * @param string $service
     * @return void
     */
    private function createFacade($facadeName, $service)
    {
        $realFacadePath = sprintf('%s/facade/%s.php', __DIR__, $facadeName);
        if (file_exists($realFacadePath)) {
            require_once($realFacadePath);
            new $facadeName($service);
            return;
        }

        pinax_dependencyInjection_FacadeBuilder::buildFacade($facadeName, $service);
    }
}
