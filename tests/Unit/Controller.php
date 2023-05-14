<?php

namespace mongosoft\soapserver\tests\Unit;

class Controller
{
    public function actions(): array
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
    public function getHello(string $name): string
    {
        return 'Hello ' . $name;
    }
}
