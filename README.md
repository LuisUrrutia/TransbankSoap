# Transbank SOAP

> Set of utilities to validate and process the XMLs of the SOAP services of Transbank

**This library IS NOT an implementation of Webpay Webservices.**

The current implementation of the utilities used in the Transbank Webservice SOAP libraries (mid 2018 and before) are modified classes of Rob Richards' libraries (wsse-php and xmlseclibs), possibly because these classes have private and unprotected methods and properties, therefore they cannot be inherited in order to use the properties.

This *library* uses a different approach after analyzing such implementation, isolating specific functionalities that must be applied to XML to build a valid request.

## Installation

```bash
composer require luisurrutia/transbank-soap
```

## Getting started

First you must import this *library* by making use of the `autoloader`that provides composer or by making individual `require` or `require_once` _(although perhaps we should kill you if you do the last)_


### Build the request

Assuming you have a class that inherits from SoapClient, you must implement the `__doRequest`method and within it, you must make use of `LuisUrrutia\TransbankSoap\Process` to build the XML of the request.

> PRIVATE_KEY should be a path of your private key or your private key itself
> 
> CERTIFICATE should be a path of your certificate or your certificate itself

```php
<?php
namespace MYPACKAGE\Transbank;

use SoapClient;
use DOMDocument;
use LuisUrrutia\TransbankSoap\Process;


class TransbankSoap extends SoapClient
{
    public function __doRequest($request, $location, $saction, $version, $one_way = null)
    {
        $process = new Process($request);
        $process->sign(PRIVATE_KEY);
        $process->addIssuer(CERTIFICATE);
        $signedRequest = $process->getXML();

        $retVal = parent::__doRequest(
            $signedRequest,
            $location,
            $saction,
            $version,
            $one_way
        );

        $doc = new DOMDocument();
        $doc->loadXML($retVal);
        return $doc->saveXML();
    }
}
```


### Validation of Response

You must get the response from your SOAP client and use `LuisUrrutia\TransbankSoap\Validation`

> $this->soapClient should be an instance of your SoapClient
> 
> TBK_CERTIFICATE should be the certificate provided by Transbank

```php
<?php
namespace MYPACKAGE\Transbank;

use LuisUrrutia\TransbankSoap\Validation;

$response = $this->soapClient->__getLastResponse();

$soapValidation = new Validation($response, TBK_CERTIFICATE);

if (!$soapValidation->isValid()) {
    throw new Exception('Invalid response or certificate');
}
```


## Built With

* [wse-php](https://github.com/robrichards/wse-php) - Libraries for adding WS-* support to ext/soap in PHP
* [xmlseclibs](https://github.com/robrichards/xmlseclibs) - A PHP library for XML Security


## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details