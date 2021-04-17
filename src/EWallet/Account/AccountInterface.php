<?php

namespace EWallet\Account;

interface AccountInterface
{
    public function getBalance(): float;

    public function increaseBalance(float $amount): float;

    public function decreaseBalance(float $amount): float;
}