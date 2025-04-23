<?php

namespace App\DataFixtures;

use AllowDynamicProperties;
use App\Entity\Pool;
use App\Entity\Song;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

#[AllowDynamicProperties] class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $pools = [];
        for ($i = 1; $i <= 10; $i++) {
            $pool = new Pool();
            $pool->setName($this->faker->name($i % 2 ? "male" : "female"));
            $pool->setCode("toto" . $i);
            $pool->setStatus('on');
            $manager->persist($pool);
            $pools[] = $pool;
        }

        for ($i = 1; $i <= 100; $i++) {
            $song = new Song();
            $song->setName($this->faker->name(gender: $i % 2 ? "male" : "female"));
            $song->setArtiste('Artiste ' . $i);
            $song->setStatus("on");
            $song->addPool($pools[array_rand($pools, 1)]);
            $manager->persist(object: $song);
        }
        /*$song->setName('Song 1');
        $song->setArtiste('Artiste 1');
        $manager->persist(object: $song);*/

        $manager->flush();
    }
}
