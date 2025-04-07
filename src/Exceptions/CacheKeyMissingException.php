<?php

namespace Thombas\RevisedServicePattern\Exceptions;

use Exception;
use \Illuminate\Http\Response;

class CacheKeyMissingException extends Exception
{
    protected $message = 'You need to setup the cache key for this service';

    protected $code = Response::HTTP_FAILED_DEPENDENCY;
}