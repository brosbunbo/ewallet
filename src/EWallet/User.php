<?php

namespace EWallet;

use Ramsey\Uuid\Uuid;

use EWallet\Account\{
    BaseAccount,
    CashAccount,
    CreditAccount
};

class User
{
    protected $uuid;

    protected $accounts = [];

    protected $topupAvailable = 1000;

    protected $withdrawAvailable = 1000;

    public function __construct()
    {
        // 1. The system will consist of the customers with their own unique customer IDs and their e-wallet.
        $this->uuid = Uuid::uuid4()->toString();
        $this->log('User created: ' . $this->uuid);

        // 2. Each e-wallet has at least 2 accounts - 1 with virtual currency (credits , 1 credit =1USD) and 1 with USD account.
        $cashAccount = new CashAccount('USD');
        $cashUuid = $cashAccount->getUuid();
        $this->log('Cash account created: ' . $cashUuid);
        $this->accounts[$cashUuid] = $cashAccount;
        $this->defaultAccount = $cashUuid;

        $creditAccount = new CreditAccount('CREDIT');
        $creditUuid = $creditAccount->getUuid();
        $this->log('Credit account created: ' . $creditUuid);
        $this->accounts[$creditUuid] = $creditAccount;
    }

    protected function validateAccount(string $source): void
    {
        if (! isset($this->accounts[$source])) {
            throw new \Exception("Invalid account {$source}. Available: " . json_encode(array_keys($this->accounts)));
        }
    }

    // 3. The user can set any of account except virtual one as a default account.
    public function setDefaultAccount(string $source): void
    {
        $this->log('Set default account: ' . $source);
        $this->validateAccount($source);

        if (! $this->accounts[$source]->ifCanBeDefault()) {
            throw new \Exception('This account can not be set as default.');
        }

        $this->defaultAccount = $source;
    }

    // 4. The user can freeze any of account except the virtual one.
    public function freezeAccount(string $source): void
    {
        $this->log('Freeze account: ' . $source);
        $this->validateAccount($source);

        if (! $this->accounts[$source]->ifCanBeFrozen()) {
            throw new \Exception('This account can not be frozen.');
        }

        $this->accounts[$source]->freeze();
    }

    // 5. The user can add more accounts in any of the currency.
    public function addAccount(string $currency): void
    {
        $account = new CashAccount($currency);
        $source = $account->getUuid();

        $this->accounts[$source] = $account;

        $this->log('Add account: '. $currency);
    }

    // 6. It should be possible to list all of the accounts of the user and get a balance of any account.
    public function listAccounts(): array
    {
        $accounts = array_map(function ($account) {
            return [
                'uuid' => $account->getUuid(),
                'currency' => $account->getCurrency(),
                'balance' => $account->getBalance()
            ];
        }, array_values($this->accounts));

        $this->log('List accounts: ' . json_encode($accounts));

        return $accounts;
    }

    // 6. It should be possible to list all of the accounts of the user and get a balance of any account.
    public function getAccountBalance(string $source): float
    {
        $this->validateAccount($source);

        $balance = $this->accounts[$source]->getBalance();

        $this->log("Get account balance {$source}: {$balance}");

        return $balance;
    }

    // 7. The user can top-up any account with any amount which is not higher than customer's top up limit (per day).
    public function topup(string $source, float $amount): void
    {
        $this->validateAccount($source);

        if ($amount > $this->topupAvailable) {
            throw new \Exception('Topup limit exceeded');
        }

        $this->accounts[$source]->increaseBalance($amount);
        $this->topupAvailable -= $amount;

        $this->log("Topup {$source} $amount");
    }

    // 8. The user can transfer amounts from one account to another.
    public function transfer(string $source, string $target, float $amount): void
    {
        $this->validateAccount($source);
        $this->validateAccount($target);

        $this->accounts[$source]->decreaseBalance($amount);

        // TODO: currency calculation
        $exchangeRate = 1;
        $this->accounts[$target]->increaseBalance($amount * $exchangeRate);

        $this->log("Transfer from {$source} to {$target}: {$amount}");
    }

    // 9. The user can withdraw money from any account not exceeding customer's withdrawal limit (per day).
    public function withdraw(string $source, float $amount): void
    {
        $this->validateAccount($source);

        if ($amount > $this->withdrawAvailable) {
            throw new \Exception('Withdraw limit exceeded');
        }

        // 10. It is not possible to do money withdrawal from virtual currency account
        if (! $this->accounts[$source]->ifCanWithdraw()) {
            throw new \Exception('Can not withdraw from this account');
        }

        $this->accounts[$source]->decreaseBalance($amount);
        $this->withdrawAvailable -= $amount;

        $this->log("Withdraw {$source} {$amount}");
    }

    protected function log(string $msg)
    {
        $fileName = 'logs.txt';
        $msg = PHP_EOL . "[" . date('Y-m-d H:i:s') . "] " . $msg;

        file_put_contents($fileName, $msg, FILE_APPEND);
    }
}