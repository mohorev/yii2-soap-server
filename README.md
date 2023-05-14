SOAP Server Extension for Yii 2
===============================

Note, PHP SOAP extension is required.

[![Latest Stable Version](https://poser.pugx.org/mongosoft/yii2-soap-server/v/stable.png)](https://github.com/mohorev/yii2-soap-server/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](docs/LICENSE.md)
[![Build Status](https://github.com/mohorev/yii2-soap-server/actions/workflows/build.yml/badge.svg)](https://github.com/mohorev/yii2-soap-server/actions/workflows/build.yml)
[![Total Downloads](https://poser.pugx.org/mongosoft/yii2-soap-server/downloads.png)](https://packagist.org/packages/mongosoft/yii2-soap-server)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist mongosoft/yii2-soap-server "*"
```

or add

```json
"mongosoft/yii2-soap-server": "*"
```

to the `require` section of your `composer.json` file.

Usage
-----

You need to add [[mongosoft\soapserver\Action]] to web controller.

Note, In a service class, a remote invokable method must be a public method with a doc
comment block containing the '@soap' tag.

```php
class ApiController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'hello' => 'mongosoft\soapserver\Action',
        ];
    }

    /**
     * @param string $name
     * @return string
     * @soap
     */
    public function getHello($name)
    {
        return 'Hello ' . $name;
    }
}
```

In case you want to disable the WSDL mode of SoapServer, you can specify this in the `serviceOptions` parameter as indicated below.
You can use this when the request is to complex for the WSDL generator.

```php
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'mongosoft\soapserver\Action',
                'serviceOptions' => [
                    'disableWsdlMode' => true,
                ]
            ]
        ];
    }
```

Testing
-------

``` bash
$ vendor/bin/codecept run Unit
```

## Contributing

Please see [CONTRIBUTING](docs/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](docs/LICENSE.md) for more information.
