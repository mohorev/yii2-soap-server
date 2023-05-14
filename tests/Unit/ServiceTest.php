<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use mongosoft\soapserver\Service;
use mongosoft\soapserver\tests\Unit\Controller;
use SimpleXMLElement;

class ServiceTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    protected function _before()
    {
    }

    // tests
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
