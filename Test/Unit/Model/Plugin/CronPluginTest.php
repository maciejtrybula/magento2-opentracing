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
use PHPUnit\Framework\TestCase;
use Trysoft\OpenTracing\Model\Plugin\CronPlugin;

/**
 * Class CronPluginTest
 */
class CronPluginTest extends TestCase
{
    /**
     * @var CronPlugin
     */
    protected $plugin;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->plugin = $this->objectManager->getObject(
            CronPlugin::class
        );
    }

    /**
     * Test around plugin on cron launch
     */
    public function testAroundLaunch()
    {
        $subject = $this->createMock(Cron::class);

        $cronLaunch = function () {
            return ResponseInterface::class;
        };

        $this
            ->assertEquals(
                ResponseInterface::class,
                $this->plugin->aroundLaunch($subject, $cronLaunch)
            );
    }
}
