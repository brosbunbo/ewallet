<?php

use EWallet\User;

require __DIR__.'/vendor/autoload.php';

// 1. The system will consist of the customers with their own unique customer IDs and their e-wallet.
// 2. Each e-wallet has at least 2 accounts - 1 with virtual currency (credits , 1 credit =1USD) and 1 with USD account.
$user = new User;

// 6. It should be possible to list all of the accounts of the user and get a balance of any account.
$accounts = $user->listAccounts();
$cashAccount = $accounts[0];
$creditAccount = $accounts[1];

// 3. The user can set any of account except virtual one as a default account.
$user->setDefaultAccount($cashAccount['uuid']);

// 4. The user can freeze any of account except the virtual one.
// $user->freezeAccount($creditAccount['uuid']);
$user->freezeAccount($cashAccount['uuid']);

// 5. The user can add more accounts in any of the currency.
$user->addAccount('EUR');

// 6. It should be possible to list all of the accounts of the user and get a balance of any account.
$user->getAccountBalance($cashAccount['uuid']);

// 7. The user can top-up any account with any amount which is not higher than customer's top up limit (per day).
$user->topup($cashAccount['uuid'], 1000);

// 8. The user can transfer amounts from one account to another.
$user->transfer($cashAccount['uuid'], $creditAccount['uuid'], 500);

// 9. The user can withdraw money from any account not exceeding customer's withdrawal limit (per day).
$user->withdraw($cashAccount['uuid'], 300);

// 10. It is not possible to do money withdrawal from virtual currency account
// $user->withdraw($creditAccount['uuid'], 300);

$user->listAccounts();