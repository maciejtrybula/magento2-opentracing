<?php
/**
 * @package  Trysoft\OpenTracing
 * @author Maciej Trybuła <maciej.trybula@gmail.com>
 */

namespace Trysoft\OpenTracing\Test\Unit\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Trysoft\OpenTracing\Service\TracerService;
use Trysoft\OpenTracing\Tracer\TracerBuilder;
use Zend\Http\Client;
use Zend\Psr7Bridge\Zend\RequestFactory;

class TracerServiceTest extends TestCase
{
    protected $objectManager;

    protected $tracerService;

    protected $tracerBuilder;

    protected $storeManager;

    protected $scopeConfigInterface;

    protected $client;

    protected $requestFactory;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->tracerBuilder = $this->getMockBuilder(TracerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $store = $this->getMockBuilder(Store::class)->disableOriginalConstructor()->getMock();

        $this->storeManager->method('getStore')->willReturn($store);

        $this->scopeConfigInterface = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestFactory = $this->getMockBuilder(RequestFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tracerService = new TracerService(
            $this->tracerBuilder,
            $this->storeManager,
            $this->scopeConfigInterface,
            $this->client,
            $this->requestFactory
        );
    }

    public function testSetGlobalTracer()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        $this->assertEquals('http://magento2-clean', $baseUrl);

    }
}