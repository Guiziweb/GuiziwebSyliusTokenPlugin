<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Command;

use Guiziweb\SyliusTokenPlugin\Wallet\TokenExpirerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'guiziweb:tokens:expire',
    description: 'Expires token batches past their expiration date and records the movement.',
)]
final class ExpireTokensCommand extends Command
{
    public function __construct(private readonly TokenExpirerInterface $tokenExpirer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $expired = $this->tokenExpirer->expire();

        (new SymfonyStyle($input, $output))->success(sprintf('%d tokens expired.', $expired));

        return Command::SUCCESS;
    }
}
