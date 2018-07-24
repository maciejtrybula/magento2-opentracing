<?php
/**
 * @package  Trysoft\OpenTracing
 * @author Maciej Trybuła <mtrybula@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Trysoft\OpenTracing\Service;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use const OpenTracing\Formats\TEXT_MAP;
use OpenTracing\Formats;
use OpenTracing\GlobalTracer;
use OpenTracing\NoopTracer;
use OpenTracing\Span;
use OpenTracing\SpanContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Trysoft\OpenTracing\Api\TracerServiceInterface;
use Trysoft\OpenTracing\Exception\OpenTracingException;
use Zend\Http\Client;
use Zend\Psr7Bridge\Zend\Request;
use ZipkinOpenTracing\Tracer;
use Trysoft\OpenTracing\Tracer\TracerBuilder;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\RequestFactory as GuzzleRequestFactory;

/**
 * Class TracerService
 */
class TracerService implements TracerServiceInterface
{
    /**
     * @var SpanContext
     */
    public $spanContext;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var TracerBuilder $tracerBuilder
     */
    protected $tracerBuilder;

    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    protected $scopeConfig;

    /**
     * @var Tracer
     */
    protected $tracer;

    /**
     * @var Span
     */
    protected $span;

    /**
     * @var Client $client
     */
    protected $client;

    /**
     * @var ClientFactory $clientFactory
     */
    private $clientFactory;

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var GuzzleRequestFactory $request
     */
    protected $requestFactory;

    /**
     * @var array $headers
     */
    protected $headers = [];

    protected $headersPsr7;

    /**
     * @var GlobalTracer $globalTracer
     */
    private $globalTracer;

    /**
     * @var string $tracerBackendRoute
     */
    private $tracerBackendRoute;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var Span $magentoLaunchSpan
     */
    private $magentoLaunchSpan;

    /**
     * TracerService constructor.
     *
     * @param TracerBuilder $tracerBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param GuzzleClient $client
     * @param GuzzleRequestFactory $requestFactory
     * @param GlobalTracer $globalTracer
     * @param ClientFactory $clientFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        TracerBuilder $tracerBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        GuzzleClient $client,
        GuzzleRequestFactory $requestFactory,
        GlobalTracer $globalTracer,
        ClientFactory $clientFactory,
        LoggerInterface $logger
    ) {
        $this->tracerBuilder = $tracerBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->globalTracer = $globalTracer;
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @throws OpenTracingException
     */
    public function setGlobalTracer()
    {
        if ($this->globalTracer::get() instanceof NoopTracer) {
            $baseUrl = null;

            try {
                $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            } catch (NoSuchEntityException $ex) {
                $this->logger->error(sprintf('Global Tracer Set exception: %s', $ex->getMessage()));
            }

            if (!$baseUrl) {
                throw new OpenTracingException('Global Tracer Failed. Check logs');
            }
            $this->setTracer($this->tracerBuilder->build($baseUrl, '127.0.0.1'));
            $this->globalTracer::set($this->getTracer());
        }

        $this->tracerBackendRoute = $this
            ->scopeConfig
            ->getValue(self::TRACER_BACKEND_ROUTE);
    }

    /**
     * @return GlobalTracer
     */
    public function getGlobalTracer(): GlobalTracer
    {
        return $this->globalTracer;
    }

    /**
     * @return void
     */
    public function setSpanContext()
    {
        $this->headers = getallheaders();
        $this->spanContext = $this
            ->globalTracer::get()
            ->extract(
                Formats\TEXT_MAP,
                $this->headers
            );
    }

    /**
     * @return SpanContext
     */
    public function getSpanContext(): SpanContext
    {
        return $this->spanContext;
    }

    /**
     * @param Span $span
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function sendSpan(Span $span): ResponseInterface
    {
        /** @var GuzzleClient $client */
        $client = $this->clientFactory->create();

        $headers = [];

        $this
            ->getTracer()
            ->inject(
                $span->getContext(),
                TEXT_MAP,
                $headers
            );

        /** @var \GuzzleHttp\Psr7\Request $request */
        $request = $this->requestFactory->create(
            [
                'method' => 'GET',
                'uri' => $this->tracerBackendRoute,
                'headers' => $headers,
            ]
        );

        return $client->send($request);
    }

    /**
     * @param Tracer $tracer
     */
    protected function setTracer($tracer)
    {
        $this->tracer = $tracer;
    }

    /**
     * @param Span $span
     */
    protected function setSpan($span)
    {
        $this->span = $span;
    }

    /**
     * @return Tracer
     */
    public function getTracer(): Tracer
    {
        return $this->tracer;
    }

    /**
     * @return Span
     */
    public function getSpan(): Span
    {
        return $this->span;
    }

    /**
     * @param Span $span
     */
    private function setMagentoLaunchSpan(Span $span)
    {
        $this->magentoLaunchSpan = $span;
    }

    /**
     * @return Span
     */
    public function getMagentoLaunchSpan(): Span
    {
        return $this->magentoLaunchSpan;
    }

    /**
     * {@inheritdoc}
     */
    public function startSpan($operationName, $options = []): Span
    {
        $span = $this->getGlobalTracer()::get()->startSpan($operationName, $options);

        if (self::MAIN_MAGENTO_LAUNCH_SPAN === $operationName) {
            $this->setMagentoLaunchSpan($span);
        }

        return $span;
    }
}
