<?php

namespace Thombas\RevisedServicePattern\Enums;

enum ServiceMethodEnum: string
{
    case Get = 'get';

    case Post = 'post';

    case Patch = 'patch';

    case Put = 'put';
}