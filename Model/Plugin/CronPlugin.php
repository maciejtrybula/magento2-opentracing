<?php
/**
 * @package Trysoft\OpenTracing
 * @author Maciej Trybuła <maciej.trybula@gmail.com>
 * @copyright 2018 Trysoft Maciej Trybuła
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Trysoft\OpenTracing\Model\Plugin;

use Magento\Framework\App\Cron;
use Magento\Framework\App\ResponseInterface;

/**
 * Class CronPlugin
 */
class CronPlugin
{
    /**
     * @param Cron $cron
     * @param callable $proceed
     *
     * @return ResponseInterface
     */
    public function aroundLaunch(Cron $cron, callable $proceed)
    {
        $result = $proceed();

        return $result;

    }
}
