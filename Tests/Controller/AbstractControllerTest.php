<?php

namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Core\GatewayInterface;
use Payum\Core\Model\Token;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractControllerTest extends TestCase
{
    protected const GATEWAY_NAME = 'theGateway';
    protected const AFTER_URL = 'http://example.com/theAfterUrl';

    protected Token $token;
    protected HttpRequestVerifierInterface $httpRequestVerifierMock;
    protected GatewayInterface $gatewayMock;
    protected RegistryInterface $registryMock;
    protected Payum $payum;
    protected Request $request;

    protected function setUp(): void
    {
        $this->request = Request::create('/');
        $this->request->query->set('foo', 'fooVal');

        $this->token = new Token;
        $this->token->setGatewayName(self::GATEWAY_NAME);
        $this->token->setAfterUrl(self::AFTER_URL);

        $this->httpRequestVerifierMock = $this->createMock(
            HttpRequestVerifierInterface::class
        );
        $this->httpRequestVerifierMock
            ->expects($this->any())
            ->method('verify')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue($this->token));

        $this->httpRequestVerifierMock
            ->expects($this->any())
            ->method('invalidate')
            ->with($this->identicalTo($this->token));

        $this->initGatewayMock();

        $this->registryMock = $this->createMock(RegistryInterface::class);
        $this->registryMock
            ->expects($this->any())
            ->method('getGateway')
            ->with(self::GATEWAY_NAME)
            ->will($this->returnValue($this->gatewayMock));

        $this->payum = new Payum(
            $this->registryMock,
            $this->httpRequestVerifierMock,
            $this->createMock(GenericTokenFactoryInterface::class),
            $this->createMock(StorageInterface::class)
        );
    }

    abstract protected function initGatewayMock();
}