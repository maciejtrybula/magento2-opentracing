<?php
/**
 * Created by PhpStorm.
 * User: nalesnik
 * Date: 16.01.18
 * Time: 22:55
 */

namespace Trysoft\OpenTracing;


use OpenTracing\Exceptions\InvalidReferencesSet;
use OpenTracing\Exceptions\InvalidSpanOption;
use OpenTracing\Exceptions\SpanContextNotFound;
use OpenTracing\Exceptions\UnsupportedFormat;
use OpenTracing\Span;
use OpenTracing\SpanContext;
use OpenTracing\SpanOptions;


use Zend\Psr7Bridge\Zend\Request;
use Zend\Http\Client;
use Zipkin\Endpoint;
use Zipkin\Reporters\Http\CurlFactory;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;




class Tracer implements \OpenTracing\Tracer
{
    function build_tracer($serviceName, $ipv4, $port = null)
    {
        $endpoint = Endpoint::create($serviceName, $ipv4, null, $port);
        $logger = new \Psr\Log\NullLogger();
        $clientFactory = CurlFactory::create();
        $reporter = new \Zipkin\Reporters\Http($clientFactory, $logger);
        $sampler = BinarySampler::createAsAlwaysSample();
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();

        return new \ZipkinOpenTracing\Tracer($tracing);
    }

    public function startSpan($operationName, $options = [])
    {
        // TODO: Implement startSpan() method.
    }

    public function inject(SpanContext $spanContext, $format, &$carrier)
    {
        // TODO: Implement inject() method.
    }

    public function extract($format, $carrier)
    {
        // TODO: Implement extract() method.
    }

    public function flush()
    {
        // TODO: Implement flush() method.
    }


    public function asd()
    {
        $tracer = build_tracer('frontend', '127.0.0.1');
        \OpenTracing\GlobalTracer::set($tracer);
        $span = $tracer->startSpan('http_request');
        usleep(100 * random_int(1, 3));
        $childSpan = $tracer->startSpan('users:get_list', [
            'child_of' => $span
        ]);
        $headers = [];
        $cookies = [];
        $tracer->inject($span->getContext(), \OpenTracing\Formats\TEXT_MAP, $headers);
        $request = new Request('GET', 'http://magento2-clean.local:9411', $headers, $cookies,[],[],[],[]);
        $client = new Client();
        $response = $client->send($request);
        echo $response->getBody();
        $childSpan->finish();
        usleep(100 * random_int(1, 3));
        $span->finish();
        register_shutdown_function(function () use ($tracer) {
            $tracer->flush();
        });
    }

}
