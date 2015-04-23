<?php

namespace mongosoft\soapserver;

use PHP2WSDL\PHPClass2WSDL;
use SoapServer;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\di\Instance;

/**
 * Service encapsulates SoapServer and provides a WSDL-based web service.
 * Service makes use of {@see PHP2WSDL} and can generate the WSDL
 * on-the-fly without requiring you to write complex WSDL.
 *
 * Note, PHP SOAP extension is required.
 *
 * @author Alexander Mohorev <dev.mohorev@gmail.com>
 */
class Service extends Component
{
    /**
     * @var string|object the web service provider class or object.
     * If specified as a class name, it can be a path alias.
     */
    public $provider;
    /**
     * @var string the URL for the Web service.
     */
    public $serviceUrl;
    /**
     * @var string the URL for WSDL.
     */
    public $wsdlUrl;
    /**
     * @var boolean indicating if the WSDL mode of SoapServer should be disabled.
     */
    public $disableWsdlMode = false;
    /**
     * @var Cache|array|string the cache object or the application component ID of the cache object.
     * The WSDL will be cached using this cache object. Note, this property has meaning only
     * in case [[cachingDuration]] set to non-zero value.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $cache = 'cache';
    /**
     * @var integer the time in seconds that the WSDL can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * @see enableCaching
     */
    public $cachingDuration = 0;
    /**
     * @var boolean whether to enable caching WSDL
     */
    public $enableCaching = false;
    /**
     * @var string encoding of the Web service. Defaults to 'utf-8'.
     */
    public $encoding = 'utf-8';
    /**
     * @var array a list of classes that are declared as complex types in WSDL.
     * This should be an array with WSDL types as keys and names of PHP classes as values.
     * A PHP class can also be specified as a path alias.
     * @see http://www.php.net/manual/en/soapserver.soapserver.php
     */
    public $classMap = [];
    /**
     * @var string actor of the SOAP service. Defaults to null.
     */
    public $actor;
    /**
     * @var string SOAP version (e.g. '1.1' or '1.2'). Defaults to null.
     */
    public $soapVersion;
    /**
     * @var integer the persistence mode of the SOAP server.
     * @see http://www.php.net/manual/en/soapserver.setpersistence.php
     */
    public $persistence;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->provider === null) {
            throw new InvalidConfigException('The "provider" property must be set.');
        }
        if ($this->serviceUrl === null) {
            throw new InvalidConfigException('The "serviceUrl" property must be set.');
        }
        if ($this->wsdlUrl === null) {
            throw new InvalidConfigException('The "wsdlUrl" property must be set.');
        }
        if ($this->enableCaching) {
            $this->cache = Instance::ensure($this->cache, Cache::className());
        }

        if (YII_DEBUG) {
            ini_set('soap.wsdl_cache_enabled', 0);
        }
    }

    /**
     * Generates the WSDL as defined by the provider.
     * The cached version may be used if the WSDL is found valid in cache.
     * @return string the generated WSDL
     */
    public function generateWsdl()
    {
        $providerClass = get_class($this->provider);
        if ($this->enableCaching) {
            $key = [
                __METHOD__,
                $providerClass,
                $this->serviceUrl,
            ];
            $result = $this->cache->get($key);
            if ($result === false) {
                $result = $this->generateWsdlInternal($providerClass, $this->serviceUrl);
                $this->cache->set($key, $result, $this->cachingDuration);
            }
            return $result;
        } else {
            return $this->generateWsdlInternal($providerClass, $this->serviceUrl);
        }
    }

    /**
     * @see Service::generateWsdl()
     */
    protected function generateWsdlInternal($className, $serviceUrl)
    {
        $wsdlGenerator = new PHPClass2WSDL($className, $serviceUrl);
        $wsdlGenerator->generateWSDL(true);
        return $wsdlGenerator->dump();
    }

    /**
     * Handles the web service request.
     */
    public function run()
    {
        header('Content-Type: text/xml;charset=' . $this->encoding);

        if ($this->disableWsdlMode) {
            $server = new SoapServer(null, array_merge(['uri' => $this->serviceUrl], $this->getOptions()));
        } else {
            $server = new SoapServer($this->wsdlUrl, $this->getOptions());
        }
        try {
            if ($this->persistence !== null) {
                $server->setPersistence($this->persistence);
            }
            if (is_string($this->provider)) {
                $provider = $this->provider;
                $provider = new $provider();
            } else {
                $provider = $this->provider;
            }
            $server->setObject($provider);

            ob_start();
            $server->handle();
            $result = ob_get_contents();
            ob_end_clean();

            return $result;
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            // We need to end application explicitly because of http://bugs.php.net/bug.php?id=49513
            $server->fault($e->getCode(), $e->getMessage());
            exit(1);
        }
    }

    /**
     * @return array options for creating SoapServer instance
     * @see http://www.php.net/manual/en/soapserver.soapserver.php
     */
    protected function getOptions()
    {
        $options = [];
        if ($this->soapVersion === '1.1') {
            $options['soap_version'] = SOAP_1_1;
        } elseif ($this->soapVersion === '1.2') {
            $options['soap_version'] = SOAP_1_2;
        }
        if ($this->actor !== null) {
            $options['actor'] = $this->actor;
        }
        foreach ($this->classMap as $type => $className) {
            if (is_int($type)) {
                $type = $className;
            }
            $options['classmap'][$type] = $className;
        }
        $options['encoding'] = $this->encoding;
        return $options;
    }
}
