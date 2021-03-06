<?php
namespace DuplicatorPro\Guzzle\Plugin\Backoff;

defined("ABSPATH") or die("");

use DuplicatorPro\Guzzle\Http\Message\RequestInterface;
use DuplicatorPro\Guzzle\Http\Message\Response;
use DuplicatorPro\Guzzle\Http\Exception\HttpException;

/**
 * Implements an exponential backoff retry strategy.
 *
 * Warning: If no decision making strategies precede this strategy in the the chain, then all requests will be retried
 */
class ExponentialBackoffStrategy extends AbstractBackoffStrategy
{
    public function makesDecision()
    {
        return false;
    }

    protected function getDelay($retries, RequestInterface $request, Response $response = null, HttpException $e = null)
    {
        return (int) pow(2, $retries);
    }
}
