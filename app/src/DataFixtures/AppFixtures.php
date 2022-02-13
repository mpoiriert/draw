<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserAddress;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tag = $adminTag = new Tag();
        $tag->setLabel('Admin');
        $manager->persist($tag);

        $tag = $inactiveTag = new Tag();
        $tag->setLabel('Inactive');
        $tag->setActive(false);
        $manager->persist($tag);

        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setPlainPassword('admin');
        $user->setLevel(User::LEVEL_ADMIN);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setTags([$adminTag]);

        $user->getAddress()->setStreet('200 Acme');

        $user->addUserAddress($userAddress = new UserAddress());
        $userAddress->getAddress()->setStreet('201 Secondary Acme');

        $manager->persist($user);

        $user = new User();
        $user->setEmail('2fa-admin@example.com');
        $user->setPlainPassword('2fa-admin');
        $user->setLevel(User::LEVEL_ADMIN);
        $user->setRoles(['ROLE_2FA_ADMIN']);

        $manager->persist($user);

        $user = new User();
        $user->setEmail('need-change-password@example.com');
        $user->setNeedChangePassword(true);
        $user->setLevel(User::LEVEL_ADMIN);
        $user->setRoles(['ROLE_ADMIN']);

        $manager->persist($user);

        foreach (range(1, 47) as $number) {
            $user = new User();
            $user->setEmail('user-'.str_pad($number, 4, '0', STR_PAD_LEFT).'@example.com');
            $user->setPlainPassword('password');
            if (1 === $number) {
                $user->setTags([$inactiveTag]);
            }
            $manager->persist($user);
        }

        $manager->flush();
    }
}
