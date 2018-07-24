<?php
/**
 * @package Trysoft\OpenTracing
 * @author Maciej Trybuła <maciej.trybula@gmail.com>
 * @copyright 2018 Trysoft Maciej Trybuła
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Trysoft\OpenTracing\Test\Unit\Tracer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Trysoft\OpenTracing\Tracer\TracerBuilder;
use Zipkin\Endpoint;
use ZipkinOpenTracing\Tracer as ZipkinTracer;

/**
 * Class TracerBuilderTest
 */
class TracerBuilderTest extends TestCase
{
    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var TracerBuilder
     */
    protected $tracerBuilder;

    /**
     * @var ZipkinTracer $tracer
     */
    protected $tracer;

    private $buildData = [
        'serviceName' => 'magento2',
        'ipv4' => '192.168.0.4',
        'ipv6' => '2001:db8:85a3::8a2e:370:7334',
        'port' => '80',
    ];

    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @var string $serviceName
     */
    private $serviceName = 'magento2';

    /**
     * @var string $ipv4
     */
    private $ipv4 = '192.168.0.4';

    /**
     * @var string $ipv6
     */
    private $ipv6 = '2001:db8:85a3::8a2e:370:7334';

    /**
     * @var string $port
     */
    private $port = '80';

    public function setUp()
    {
        $this->logger =
            $this
                ->getMockBuilder(LoggerInterface::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->objectManager = new ObjectManager($this);

        $this->tracerBuilder = $this->objectManager->getObject(
            TracerBuilder::class,
            [
                'logger' => $this->logger,
                'data' => $this->buildData,
            ]
        );
    }

    public function testBuild()
    {
        $this->assertInstanceOf(
            ZipkinTracer::class,
            $this
                ->tracerBuilder
                ->build($this->serviceName, $this->ipv4, $this->ipv6, $this->port)
        );
    }

    public function testBuildWithoutIpv6()
    {
        $this->assertInstanceOf(
            ZipkinTracer::class,
            $this
                ->tracerBuilder
            ->build($this->serviceName, $this->ipv4, null, $this->port)
        );
    }

    public function testBuildWithoutPort()
    {
        $this->assertInstanceOf(
            ZipkinTracer::class,
            $this->tracerBuilder->build($this->serviceName, $this->ipv4, null, null)
        );
    }
}
