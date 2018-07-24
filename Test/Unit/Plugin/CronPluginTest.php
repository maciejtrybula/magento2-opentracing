<?php
/**
 * @package Trysoft\OpenTracing
 * @author Maciej Trybuła <maciej.trybula@gmail.com>
 * @copyright 2018 Trysoft Maciej Trybuła
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Trysoft\OpenTracing\Test\Unit\Model\Plugin;

use Magento\Framework\App\Cron;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Trysoft\OpenTracing\Plugin\CronPlugin;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Trysoft\OpenTracing\Tracer\TracerBuilder;
use ZipkinOpenTracing\Tracer as ZipkinTracer;

/**
 * Class CronPluginTest
 */
class CronPluginTest extends TestCase
{
    /**
     * @var TracerBuilder $tracerBuilder
     */
    protected $tracerBuilder;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var Cron $cron
     */
    protected $cron;

    /**
     * @var CronPlugin $cronPlugin
     */
    protected $cronPlugin;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $result;

    /**
     * @var ZipkinTracer $tracer
     */
    protected $tracer;

    /**
     * @var Cron|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp()
    {
        $this->subjectMock = $this->getMockBuilder(Cron::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->result = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();


        $this->cronPlugin = $this->getMockBuilder(CronPlugin::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test around plugin on cron launch
     */
    public function testAroundLaunch()
    {
        $subjectMock = $this->getMockBuilder(Cron::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subjectMock->expects($this->once())
            ->method('launch')
            ->willReturn('ResponseInterface');


        $closureMock = function () use ($subjectMock) {
            return $subjectMock;
        };

        $this->assertEquals(
            $subjectMock,
            $this->cronPlugin->aroundLaunch($subjectMock, $closureMock)
        );
    }
}
