<?php

namespace EWallet\Account;

class CashAccount extends BaseAccount
{
    protected bool $canBeDefault = true;

    protected bool $canBeFrozen = true;

    protected bool $canWithdraw = true;
}