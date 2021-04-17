<?php

namespace EWallet\Account\Traits;

trait CanBeFrozen
{
    protected bool $canBeFrozen = false;

    protected bool $isFrozen = false;

    public function ifCanBeFrozen(): bool
    {
        return $this->canBeFrozen;
    }

    public function ifIsFrozen(): bool
    {
        return $this->isFrozen;
    }

    public function freeze(): void
    {
        $this->isFrozen = true;
    }

    public function unfreeze(): void
    {
        $this->isFrozen = false;
    }
}