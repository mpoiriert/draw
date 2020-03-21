<?php namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setPlainPassword('admin');
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        foreach (range(1, 49) as $number) {
            $user = new User();
            $user->setEmail('user-' . str_pad($number, 4, '0', STR_PAD_LEFT) . '@example.com');
            $user->setPlainPassword('password');
            $manager->persist($user);
        }

        $manager->flush();
    }
}
