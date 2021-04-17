<?php

namespace EWallet\Account\Traits;

trait CanWithdraw
{
    protected bool $canWithdraw = false;

    public function ifCanWithdraw(): bool
    {
        return $this->canWithdraw;
    }
}