<?php
/**
 * @package Trysoft\OpenTracing
 * @author Maciej Trybuła <maciej.trybula@gmail.com>
 * @copyright 2018 Trysoft Maciej Trybuła
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Trysoft\OpenTracing\Api;

use OpenTracing\Exceptions\InvalidReferencesSet;
use OpenTracing\Exceptions\InvalidSpanOption;
use OpenTracing\GlobalTracer;
use OpenTracing\Span;
use OpenTracing\SpanContext;
use OpenTracing\StartSpanOptions;
use Psr\Http\Message\ResponseInterface;
use Trysoft\OpenTracing\Exception\OpenTracingException;
use ZipkinOpenTracing\Tracer;

/**
 * Interface TracerServiceInterface
 */
interface TracerServiceInterface
{
    const TRACER_BACKEND_ROUTE = 'opentracing/opentracing_group/tracer_backend_route';
    const MAIN_MAGENTO_LAUNCH_SPAN = 'magento_launch';

    /**
     * @return $this
     *
     * @throws OpenTracingException
     */
    public function setGlobalTracer();

    /**
     * @return GlobalTracer
     */
    public function getGlobalTracer(): GlobalTracer;

    /**
     * @return Tracer
     */
    public function getTracer(): Tracer;

    /**
     * @return Span
     */
    public function getSpan(): Span;

    /**
     * @return SpanContext
     */
    public function getSpanContext(): SpanContext;

    /**
     * @return void
     */
    public function setSpanContext();

    /**
     * @param Span $span
     *
     * @return ResponseInterface
     */
    public function sendSpan(Span $span): ResponseInterface;

    /**
     * @return Span
     */
    public function getMagentoLaunchSpan(): Span;

    /**
     * Mock for \Opentracing\Tracer::startSpan to intercept magento launch span
     *
     * @param string $operationName
     * @param array|StartSpanOptions $options
     *
     * @return Span
     * @throws InvalidSpanOption for invalid option
     * @throws InvalidReferencesSet for invalid references set
     */
    public function startSpan($operationName, $options = []): Span;
}
