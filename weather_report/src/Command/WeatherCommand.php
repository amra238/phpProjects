<?php

namespace App\Command;

use App\Service\WeatherService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather',
    description: 'get weather data about a given location.',
)]
class WeatherCommand extends Command
{
    public function __construct(private WeatherService $weatherService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('city', InputArgument::OPTIONAL, 'Название города');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $city = $input->getArgument('city');

        if (!$city) {
            $question = new Question('Введите название города: ');
            $city = $io->askQuestion($question);

            if (!$city) {
                $io->error('Город не указан.');
                return Command::FAILURE;
            }
        }

        $io->info("Запрашиваю погоду для {$city}");

        try {
            $weather = $this->weatherService->getWeather($city);
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success("Погода в {$weather['city']}, {$weather['country']}");
        $io->info($weather['message']);

        $io->table(
            ['Параметр', 'Значение'],
            [
                ['погода', $weather['weather']],
                ['Температура', "{$weather['temp']}°C"],
                ['Ощущается как',  "{$weather['feels_like']}°C"],
                ['Описание', $weather['description']],
                ['Влажность', "{$weather['humidity']}%"],
                ['Ветер', "{$weather['wind_speed']} м/с"]
            ]
        );

        return Command::SUCCESS;
    }
}
