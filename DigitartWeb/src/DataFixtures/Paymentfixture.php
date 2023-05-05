<?php

namespace App\DataFixtures;

use App\Entity\Payment;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class Paymentfixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $userIds = [1, 75, 76, 77, 78];
        $paymentId = 20;

        for ($i = 0; $i < 50; $i++) {
            $payment = new Payment();
            $payment->setPaymentId($paymentId++);
            $user = $manager->getRepository(Users::class)->find($faker->randomElement($userIds));
            $payment->setUser($user);
            $payment->setPurchaseDate($faker->dateTimeBetween('-1 year', 'now'));
            $payment->setNbAdult($faker->numberBetween(0, 10));
            $payment->setNbTeenager($faker->numberBetween(0, 10));
            $payment->setNbStudent($faker->numberBetween(0, 10));
            $payment->setTotalPayment($faker->numberBetween(20, 250));
            $payment->setPaid($faker->boolean(70));

            $manager->persist($payment);
        }

        $manager->flush();
    }
}
