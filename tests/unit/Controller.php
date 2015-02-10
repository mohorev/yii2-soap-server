<?php

namespace mongosoft\soapserver\tests\unit;

class Controller
{
    public function actions()
    {
        return [
            'hello' => [
                'class' => 'mongosoft\soapserver\Action',
            ],
        ];
    }

    /**
     * @param string $name Your name
     * @return string
     * @soap
     */
    public function getHello($name)
    {
        return 'Hello ' . $name;
    }
}
