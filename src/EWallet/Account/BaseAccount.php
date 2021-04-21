<?php

namespace EWallet\Account;

use Ramsey\Uuid\Uuid;

class BaseAccount implements AccountInterface
{
    use Traits\CanBeDefault;
    use Traits\CanBeFrozen;
    use Traits\CanWithdraw;

    protected $uuid;

    protected $currency;

    protected $balance = 0;

    public function __construct(string $currency)
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->currency = $currency;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function increaseBalance(float $amount): float
    {
        if ($amount <= 0) {
            throw new \Exception('Amount must be positive');
        }

        $this->balance += $amount;

        return $this->balance;
    }

    public function decreaseBalance(float $amount): float
    {
        if ($amount <= 0) {
            throw new \Exception('Amount must be positive');
        }

        if ($this->balance < $amount) {
            throw new \Exception('Invalid balance amount.');
        }

        $this->balance -= $amount;

        return $this->balance;
    }
}