<?php

namespace Thombas\RevisedServicePattern\Templates;

use Illuminate\Validation\Validator;

abstract class BaseTemplate
{
    public function __construct()
    {
        $this->validate();
    }

    public function get(
        string $key
    ): mixed {
        return $this()[$key];
    }

    public function toArray(): array
    {
        return $this();
    }

    abstract protected function validator(): ?Validator;

    protected function validate(): void
    {
        $this->validator()?->validate();
    }
}