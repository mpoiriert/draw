<?php namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tag = new Tag();
        $tag->setLabel('Admin');
        $manager->persist($tag);

        $tag = new Tag();
        $tag->setLabel('Inactive');
        $tag->setActive(false);
        $manager->persist($tag);

        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setPlainPassword('admin');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setTags([$tag]);

        $user->getAddress()->setStreet('200 Acme');

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
