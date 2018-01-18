<?php
/**
 * @package Trysoft\OpenTracing
 * @author Maciej Trybuła <mtrybula@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Trysoft\OpenTracing\Plugin;

use Magento\Framework\Event\Manager;
use const OpenTracing\Tags\SPAN_KIND;
use Trysoft\OpenTracing\Api\TracerServiceInterface;

/**
 * Class EventDispatchPlugin
 */
class EventDispatchPlugin
{
    /**
     * @var TracerServiceInterface $tracerService
     */
    private $tracerService;

    /**
     * EventDispatchPlugin constructor.
     *
     * @param TracerServiceInterface $tracerService
     */
    public function __construct(TracerServiceInterface $tracerService)
    {
        $this->tracerService = $tracerService;
    }

    /**
     * @param Manager $manager
     * @param $eventName
     * @param array $data
     * @param callable $proceed
     */
    public function aroundDispatch(Manager $manager, callable $proceed, $eventName, array $data = [])
    {
        $magentoSpan = $this->tracerService->getMagentoLaunchSpan();

        $childOf = $magentoSpan ? ['child_of' => $magentoSpan] : [];

        $eventSpan = $this->tracerService->startSpan('event_' . $eventName, $childOf);

        if (!$eventSpan) {
            return;
        }

        $eventSpan->setTag(SPAN_KIND, 'SERVER');
        $this->tracerService->sendSpan($eventSpan);

        $proceed($eventName, $data);

        $eventSpan->finish();
    }
}
