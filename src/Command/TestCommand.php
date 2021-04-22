<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\{
    ChoiceQuestion,
    Question
};

use EWallet\User;

class TestCommand extends Command
{
    protected static $defaultName = 'test';

    protected ?InputInterface $input;

    protected ?OutputInterface $output;

    protected ?User $user = null;

    protected function configure()
    {
        $this->setDescription('Run ewallet test.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->menuMain();

        return Command::SUCCESS;
    }

    protected function menuMain(): void
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            '<info>Select action</info>',
            [
                'Create user',
                'Set default account',
                'Freeze account',
                'Add account',
                'List account',
                'Get account balance',
                'Topup',
                'Transfer',
                'Withdraw'
            ]
        );
        $question->setErrorMessage('Invalid action');

        $action = $helper->ask($this->input, $this->output, $question);
        if ('Create user' === $action) {
            $this->user = new User;
        } else {
            if (! $this->user) {
                $this->writeError('You need to create an user first');
            } else {
                if ('Set default account' === $action) {
                    $this->menuDefaultAccount();
                } else if ('Freeze account' === $action) {
                    $this->menuFreezeAccount();
                } else if ('Add account' === $action) {
                    $this->menuAddAccount();
                } else if ('List account' === $action) {
                    $this->menuListAccount();
                } else if ('Get account balance' === $action) {
                    $this->menuGetBalance();
                } else if ('Topup' === $action) {
                    $this->menuTopup();
                } else if ('Transfer' === $action) {
                    $this->menuTransfer();
                } else if ('Withdraw' === $action) {
                    $this->menuWithdraw();
                }
            }
        }

        $this->menuMain();
    }

    protected function menuDefaultAccount(): void
    {
        $accUuid = $this->askForAccountSelection();
        if ('Back' !== $accUuid) {
            try {
                $this->user->setDefaultAccount($accUuid);
            } catch(\Exception $e) {
                $this->writeError($e->getMessage());
            }
        }

    }

    protected function menuFreezeAccount(): void
    {
        $accUuid = $this->askForAccountSelection();
        if ('Back' !== $accUuid) {
            try {
                $this->user->freezeAccount($accUuid);
            } catch(\Exception $e) {
                $this->writeError($e->getMessage());
            }
        }
    }

    protected function menuAddAccount(): void
    {
        $helper = $this->getHelper('question');
        $q = new Question('Please enter the currency:');

        $currency = $helper->ask($this->input, $this->output, $q);
        try {
            $this->user->addAccount($currency);
        } catch(\Exception $e) {
            $this->writeError($e->getMessage());
        }
    }

    protected function menuListAccount(): void
    {
        $accounts = $this->user->listAccounts();
        foreach ($accounts as $acc) {
            $this->writeComment($acc['uuid'] . ' (' . $acc['currency'] . ') - ' . $acc['balance']);
        }
    }

    protected function menuGetBalance(): void
    {
        $accUuid = $this->askForAccountSelection();
        if ('Back' !== $accUuid) {
            $this->user->getAccountBalance($accUuid);
        }
    }

    protected function menuTopup(): void
    {
        $accUuid = $this->askForAccountSelection();
        if ('Back' !== $accUuid) {
            $amount = $this->askForAmount();

            try {
                $this->user->topup($accUuid, $amount);
            } catch(\Exception $e) {
                $this->writeError($e->getMessage());
            }
        }
    }

    protected function menuTransfer(): void
    {
        $sourceUuid = $this->askforAccountSelection();
        if ('Back' !== $sourceUuid) {
            $targetUuid = $this->askForAccountSelection();

            if ('Back' !== $targetUuid) {
                $amount = $this->askForAmount();

                try {
                    $this->user->transfer($sourceUuid, $targetUuid, $amount);
                } catch(\Exception $e) {
                    $this->writeError($e->getMessage());
                }
            }
        }
    }

    protected function menuWithdraw(): void
    {
        $accUuid = $this->askForAccountSelection();
        if ('Back' !== $accUuid) {
            $amount = $this->askForAmount();

            try {
                $this->user->withdraw($accUuid, $amount);
            } catch(\Exception $e) {
                $this->writeError($e->getMessage());
            }
        }
    }

    protected function askForAccountSelection(): string
    {
        $helper = $this->getHelper('question');

        $accounts = $this->user->listAccounts();
        $options = array_map(function ($acc) {
            return $acc['uuid'];
        }, $accounts);
        array_unshift($options, 'Back');

        $q = new ChoiceQuestion(
            '<info>Select account</info>',
            $options
        );
        $q->setErrorMessage('Invalid account');

        return $helper->ask($this->input, $this->output, $q);
    }

    protected function askForAmount(): float
    {
        $helper = $this->getHelper('question');
        $q = new Question('Please enter the amount:');

        $amount = $helper->ask($this->input, $this->output, $q);

        return (float) $amount;
    }

    protected function writeError(string $msg): void
    {
        $this->output->writeln('<error>' . $msg . '</error>');
    }

    protected function writeInfo(string $msg): void
    {
        $this->output->writeln('<info>' . $msg . '</info>');
    }

    protected function writeComment(string $msg): void
    {
        $this->output->writeln('<comment>' . $msg . '</comment>');
    }
}