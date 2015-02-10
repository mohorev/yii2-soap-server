<?php

namespace mongosoft\soapserver\tests\unit;

use Codeception\TestCase\Test;
use SimpleXMLElement;
use mongosoft\soapserver\Service;

class ServiceTest extends Test
{
    public function testGenerateWsdl()
    {
        $controller = new Controller();
        $soapService = new Service([
            'provider' => $controller,
            'serviceUrl' => 'http://test-url/',
            'wsdlUrl' => 'http://wsdl-url/',
        ]);
        $wsdl = $soapService->generateWsdl();
        $xml = simplexml_load_string($wsdl);

        $this->assertTrue($xml instanceof SimpleXMLElement);
        $this->assertEquals('definitions', (string) $xml->getName());

        $operation = $xml->xpath('//wsdl:operation[@name="getHello"]');
        $this->assertTrue($operation[0] instanceof SimpleXMLElement);

        $address = $xml->xpath('//soap:address');
        $location = (string) $address[0]->attributes()->location;
        $this->assertEquals('http://test-url/', $location);
    }
}
