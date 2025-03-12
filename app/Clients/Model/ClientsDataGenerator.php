<?php

declare(strict_types=1);

namespace App\Clients\Model;

use Core\App\App;
use Core\Config\Config;
use Faker\Factory;
use Faker\Generator;
use Iterator;

class ClientsDataGenerator
{
    private ?Generator $faker = null;

    public int $maxCount = 2000;

    public function generateClientsDataFile(int $count): bool
    {
        $fileName = Config::get('import.clients.fileName');
        $columns = Config::get('import.clients.columns');

        if ($count < 1 || $count > $this->maxCount || !$fileName || !$columns) {
            return false;
        }

        $fp = fopen(App::BASE_APP_DIR . 'import/' . $fileName, 'w');

        if ($fp === false) {
            return false;
        }

        fputcsv($fp, $columns, ',', '"', '');

        foreach ($this->generateFakeData($count - 1) as $value) {
            fputcsv($fp, $value, ',', '"', '');
        }

        fclose($fp);

        return true;
    }

    public function generateFakeData(int $count): Iterator
    {
        for ($i = 0; $i <= $count; $i++) {
            if (!$this->faker) {
                $this->faker = Factory::create();
            }

            $data =  [
                'country' => $this->faker->country(),
                'city' => $this->faker->city(),
                'isActive' => $this->faker->randomElement(['true', 'false']),
                'gender' => $this->faker->randomElement(['male', 'female']),
                'birthDate' => $this->faker->date('Y-m-d', '-18 years'),
                'salary' => $this->faker->numberBetween(1, 9999),
                'hasChildren' => $this->faker->randomElement(['true', 'false']),
                'familyStatus' => $this->faker->randomElement(['single', 'married', 'divorced']),
                'registrationDate' => $this->faker->dateTimeBetween('-10 years')->format('Y-m-d'),
            ];

            yield $data;
        }
    }
}
