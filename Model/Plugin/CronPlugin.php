<?php
/**
 * @package  magento2-clean
 * @author Maciej Trybuła <mtrybula@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
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
