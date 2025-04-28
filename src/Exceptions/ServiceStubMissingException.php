<?php

namespace Thombas\RevisedServicePattern\Exceptions;

use Exception;
use \Illuminate\Http\Response;

class ServiceStubMissingException extends Exception
{
    protected $message = 'A stub has not been set for this service';

    protected $code = Response::HTTP_FAILED_DEPENDENCY;
}