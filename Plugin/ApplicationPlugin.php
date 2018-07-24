<?php
/**
 * @package Trysoft\OpenTracing
 * @author Maciej Trybuła <maciej.trybula@gmail.com>
 * @copyright 2018 Trysoft Maciej Trybuła
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Trysoft\OpenTracing\Plugin;

use Magento\Framework\App\Http;
use OpenTracing\Span;
use const OpenTracing\Tags\SPAN_KIND;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Trysoft\OpenTracing\Api\TracerServiceInterface;
use Trysoft\OpenTracing\Exception\OpenTracingException;

class ApplicationPlugin
{
    /**
     * @var TracerServiceInterface $tracerService
     */
    private $tracerService;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * ApplicationPlugin constructor.
     *
     * @param TracerServiceInterface $tracerService
     */
    public function __construct(TracerServiceInterface $tracerService, LoggerInterface $logger)
    {
        $this->tracerService = $tracerService;
        $this->logger = $logger;
    }

    /**
     * @param Http $http
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundLaunch(Http $http, callable $proceed)
    {
        $magentoLaunchSpan = null;

        try {
            $this->tracerService->setGlobalTracer();

            $magentoLaunchSpan = $this->tracerService->startSpan(TracerServiceInterface::MAIN_MAGENTO_LAUNCH_SPAN);

            $magentoLaunchSpan->setTag(SPAN_KIND, 'SERVER');
        } catch (OpenTracingException $ex) {
            $this->logger->error(sprintf('Around Launch Open Tracing exception: %s', $ex->getMessage()));
        } catch (\Exception $ex) {
            $this->logger->error(sprintf('Around Launch exception: %s', $ex->getMessage()));
        }

        if ($magentoLaunchSpan) {
            /** @var ResponseInterface $parentResponse */
            $this->tracerService->sendSpan($magentoLaunchSpan);
        }

        $response = $proceed();

        if ($magentoLaunchSpan) {
            $magentoLaunchSpan->finish();
        }

        $tracer = $this->tracerService->getTracer();

        register_shutdown_function(
            function () use ($tracer) {
                $tracer->flush();
            }
        );

        return $response;
    }
}
