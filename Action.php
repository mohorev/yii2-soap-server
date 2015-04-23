<?php

namespace mongosoft\soapserver;

use Yii;
use yii\web\Response;

/**
 * Action is the base class for action classes that implement SOAP API.
 *
 * Action serves for two purposes. On the one hand, it displays the WSDL content specifying the Web service APIs.
 * On the other hand, it invokes the requested Web service API. A GET parameter named <code>ws</code> is used
 * to differentiate these two aspects: the existence of the GET parameter indicates performing the latter action.
 *
 * Note, PHP SOAP extension is required for this action.
 *
 * @property Service $service The Web service instance. This property is read-only.
 *
 * @author Alexander Mohorev <dev.mohorev@gmail.com>
 */
class Action extends \yii\base\Action
{
    /**
     * @var mixed the Web service provider object or class name.
     * If specified as a class name, it can be a path alias.
     * Defaults to null, meaning the current controller is used as the service provider.
     */
    public $provider;
    /**
     * @var string the URL for the Web service. Defaults to null, meaning
     * the URL for this action is used to provide Web services.
     * In this case, a GET parameter named {@link serviceVar} will be used to
     * determine whether the current request is for WSDL or Web service.
     */
    public $serviceUrl;
    /**
     * @var array the initial property values for the {@link Service} object.
     * The array keys are property names of {@link Service} and the array values
     * are the corresponding property initial values.
     */
    public $serviceOptions = [];
    /**
     * @var string the name of the GET parameter that differentiates a WSDL request
     * from a Web service request. If this GET parameter exists, the request is considered
     * as a Web service request; otherwise, it is a WSDL request.  Defaults to 'ws'.
     */
    public $serviceVar = 'ws';
    /**
     * @var string the URL for WSDL. Defaults to null, meaning
     * the URL for this action is used to serve WSDL document.
     */
    public $wsdlUrl;
    /**
     * @var array a list of PHP classes that are declared as complex types in WSDL.
     * This should be an array with WSDL types as keys and names of PHP classes as values.
     * A PHP class can also be specified as a path alias.
     * @see http://www.php.net/manual/en/soapclient.soapclient.php
     */
    public $classMap;

    /**
     * @var Service the SOAP service instance.
     */
    private $_service;


    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->provider === null) {
            $this->provider = $this->controller;
        }
        if ($this->serviceUrl === null) {
            $this->serviceUrl = Yii::$app->getUrlManager()->createAbsoluteUrl([$this->getUniqueId(), $this->serviceVar => 1]);
        }
        if ($this->wsdlUrl === null) {
            $this->wsdlUrl = Yii::$app->getUrlManager()->createAbsoluteUrl($this->getUniqueId());
        }

        // Disable CSRF (Cross-Site Request Forgery) validation for this action.
        Yii::$app->getRequest()->enableCsrfValidation = false;
    }

    /**
     * Runs the action.
     * If the GET parameter {@link serviceVar} exists, the action handle the remote method invocation.
     * If not, the action will serve WSDL content;
     */
    public function run()
    {
        Yii::$app->getResponse()->format = Response::FORMAT_RAW;

        if (Yii::$app->request->get($this->serviceVar, false)) {
            return $this->getService()->run();
        } else {
            $response = Yii::$app->getResponse();
            $response->getHeaders()->set('Content-Type', 'application/xml; charset=' . $response->charset);
            return $this->getService()->generateWsdl();
        }
    }

    /**
     * Returns the Web service instance currently being used.
     * @return Service the Web service instance
     */
    public function getService()
    {
        if ($this->_service === null) {
            $this->_service = new Service([
                'provider' => $this->provider,
                'serviceUrl' => $this->serviceUrl,
                'wsdlUrl' => $this->wsdlUrl
            ]);
            if (is_array($this->classMap)) {
                $this->_service->classMap = $this->classMap;
            }
            foreach ($this->serviceOptions as $name => $value) {
                $this->_service->$name = $value;
            }
        }
        return $this->_service;
    }
}
