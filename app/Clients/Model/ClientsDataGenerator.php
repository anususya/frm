<?php

namespace App\Clients\Model;

use App;
use Core\Config\Config;
use Faker\Factory;
use Faker\Generator;
use Iterator;

class ClientsDataGenerator
{
    private ?Generator $faker = null;

    /**
     * @param int $count
     *
     * @return bool
     */
    public function generateClientsDataFile(int $count): bool
    {
        $config = Config::getConfig('import');
        if ($count > 0 && isset($config['clients']['fileName']) && isset($config['clients']['columns'])) {
            $fp = fopen(App::BASE_APP_DIR . '/import/' . $config['clients']['fileName'], 'w');
            if ($fp !== false) {
                $value = $config['clients']['columns'];
                fputcsv($fp, $value, ',', '"', '');

                foreach ($this->generateFakeData($count - 1) as $value) {
                    fputcsv($fp, $value, ',', '"', '');
                }

                fclose($fp);

                return true;
            }
        }

        return false;
    }

    /**
     * @param int $count
     *
     * @return Iterator
     */
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
