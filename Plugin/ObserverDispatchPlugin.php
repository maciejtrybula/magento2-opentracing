<?php
/**
 * @package  Trysoft\OpenTracing
 * @author Maciej Trybuła <maciej.trybula@gmail.com>
 */

namespace Trysoft\OpenTracing\Plugin;

use Magento\Framework\Event\Invoker\InvokerDefault;
use Magento\Framework\Event\Observer;
use const OpenTracing\Tags\SPAN_KIND;
use Trysoft\OpenTracing\Api\TracerServiceInterface;

/**
 * Class ObserverDispatchPlugin
 */
class ObserverDispatchPlugin
{
    /**
     * @var TracerServiceInterface $tracerService
     */
    private $tracerService;

    /**
     * ObserverDispatchPlugin constructor.
     *
     * @param TracerServiceInterface $tracerService
     */
    public function __construct(TracerServiceInterface $tracerService)
    {
        $this->tracerService = $tracerService;
    }

    /**
     * @param InvokerDefault $invokerDefault
     * @param callable $proceed
     * @param array $configuration
     * @param Observer $observer
     */
    public function aroundDispatch(InvokerDefault $invokerDefault, callable $proceed, array $configuration, Observer $observer)
    {
        $eventSpan = $this->tracerService->getEventSpan();
        $eventName = $observer->getEvent()->getName();
        $observerName = $configuration['name'];

        $eventNameCondition = 'event_' . $eventName === $eventSpan->getOperationName();

        $childOf = ($eventSpan && $eventNameCondition) ? ['child_of' => $eventSpan] : [];

        $observerSpan = $this->tracerService->startSpan('observer_' . $observerName, $childOf);

        if (!$observerSpan) {
            return;
        }

        $observerSpan->setTag(SPAN_KIND, 'SERVER');
        $observerSpan->setTag('obs.instance', $configuration['instance']);

        if ($eventNameCondition) {
            $observerSpan->setTag('obs.event', $eventName);
        }

        $this->tracerService->sendSpan($observerSpan);

        $proceed($configuration, $observer);

        $observerSpan->finish();
    }
}
