<?php

namespace EWallet\Account\Traits;

trait CanBeDefault
{
    protected bool $canBeDefault = false;

    public function ifCanBeDefault(): bool
    {
        return $this->canBeDefault;
    }
}