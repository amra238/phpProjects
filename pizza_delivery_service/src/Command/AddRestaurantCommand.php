<?php

namespace App\Command;

use App\Entity\Point;
use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'restaurant:add',
    description: 'Добавляет ресторан с координатами в базу',
)]
class AddRestaurantCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Название ресторана')
            ->addArgument('lat', InputArgument::REQUIRED, 'Широта')
            ->addArgument('lng', InputArgument::REQUIRED, 'Долгота');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $lat = (float) $input->getArgument('lat');
        $lng = (float) $input->getArgument('lng');

        $io = new SymfonyStyle($input, $output);

        try {
            $restaurant = new Restaurant();
            $restaurant->setName($name);
            $restaurant->setLocation(new Point($lat, $lng));

            $this->entityManager->persist($restaurant);
            $this->entityManager->flush();

            $io->success("Ресторан {$name} добавлен с координатами: {$lat}, {$lng}");

            return Command::SUCCESS;
        }
        catch (\Exception $exception){
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
}
