<?php
/**
 * @package Trysoft\OpenTracing
 * @author Maciej Trybuła <maciej.trybula@gmail.com>
 * @copyright 2018 Trysoft Maciej Trybuła
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Trysoft\OpenTracing\Tracer;

use Psr\Log\LoggerInterface;
use Zipkin\Endpoint;
use Zipkin\Reporters\Http;
use Zipkin\Reporters\Http\CurlFactory;
use Zipkin\TracingBuilder;
use ZipkinOpenTracing\Tracer;
use Zipkin\Samplers\BinarySampler;

/**
 * Class TracerBuilder
 */
class TracerBuilder
{
    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * TracerBuilder constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Tracer builder
     *
     * @param string $serviceName
     * @param string $ipv4
     * @param string|null $ipv6
     * @param string|null $port
     *
     * @return Tracer
     * @throws \BadFunctionCallException
     * @throws \InvalidArgumentException
     */
    public function build(string $serviceName, string $ipv4, string $ipv6 = null, string $port = null): Tracer
    {
        $endpoint = Endpoint::create($serviceName, $ipv4, $ipv6, $port);
        $clientFactory = CurlFactory::create();
        $reporter = new Http($clientFactory, [$this->logger]);
        $sampler = BinarySampler::createAsAlwaysSample();
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();

        return new Tracer($tracing);
    }
}
