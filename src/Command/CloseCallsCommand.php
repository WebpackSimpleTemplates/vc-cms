<?php

namespace App\Command;

use App\Entity\Call;
use App\Repository\CallRepository;
use App\Repository\PushRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:close-calls',
    description: '--',
)]
class CloseCallsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CallRepository $callRepository,
        private PushRepository $pushRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $calls = $this->callRepository->getActiveMany()->getQuery()->getResult();

        /** @var Call $call */
        foreach ($calls as $call) {
            $call->setIsAutoClosed(true);
            $call->setClosedAt(new DateTime());
        }

        $this->entityManager->flush();


        /** @var Call $call */
        foreach ($calls as $call) {
            $this->pushRepository->push("calls/".$call->getId(), "closed", $call);
            $this->pushRepository->push("", "call-closed", $call);
        }

        return Command::SUCCESS;
    }
}
