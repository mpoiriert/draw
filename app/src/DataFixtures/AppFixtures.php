<?php

namespace App\DataFixtures;

use App\Entity\ChildObject2;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserAddress;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\DoctrineExtra\Common\DataFixtures\ObjectReferenceTrait;

class AppFixtures extends Fixture
{
    use ObjectReferenceTrait;

    public function load(ObjectManager $manager): void
    {
        $tag = $adminTag = new Tag();
        $tag->setLabel('Admin');
        $manager->persist($tag);

        $tag = $inactiveTag = new Tag();
        $tag->setLabel('Inactive');
        $tag->setActive(false);
        $manager->persist($tag);

        $tag = new Tag();
        $tag->setLabel('NotUse');
        $manager->persist($tag);

        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setPlainPassword('admin');
        $user->setLevel(User::LEVEL_ADMIN);
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setTags([$adminTag]);

        $user->getAddress()->setStreet('200 Acme');

        $user->addUserAddress($userAddress = new UserAddress());
        $userAddress->getAddress()->setStreet('201 Secondary Acme');

        $manager->persist($user);

        $this->assignOnDeleteObject($manager, $user);

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

        $user = new User();
        $user->setEmail('locked@example.com');
        $user->setPlainPassword('locked');
        $user->setLevel(User::LEVEL_ADMIN);
        $user->setRoles(['ROLE_ADMIN']);

        $user->setManualLock(true);

        $manager->persist($user);

        foreach (range(1, 4) as $number) {
            $user = new User();
            $user->setEmail('user-'.str_pad((string) $number, 4, '0', \STR_PAD_LEFT).'@example.com');
            $user->setPlainPassword('password');
            if (1 === $number) {
                $user->setTags([$inactiveTag]);
                $user->setPreferredLocale('fr');
            }
            $manager->persist($user);
        }

        $manager->persist(
            (new Config())
                ->setId('acme_demo')
                ->setValue(['enabled' => false, 'limit' => 10])
        );

        $manager->flush();
    }

    private function assignOnDeleteObject(ObjectManager $manager, User $user): void
    {
        $objects = [];

        $user
            ->setOnDeleteRestrict(
                $object = (new ChildObject2())->setAttribute2('on-delete-restrict')
            );

        $objects[] = $object;

        $user
            ->setOnDeleteCascade(
                $object = (new ChildObject2())->setAttribute2('on-delete-cascade')
            );

        $objects[] = $object;

        $user
            ->setOnDeleteSetNull(
                $object = (new ChildObject2())->setAttribute2('on-delete-set-null')
            );

        $objects[] = $object;

        $user
            ->setOnDeleteCascadeConfigOverridden(
                $object = (new ChildObject2())->setAttribute2('on-delete-cascade-config-overridden')
            );

        $objects[] = $object;

        $user
            ->setOnDeleteCascadeAttributeOverridden(
                $object = (new ChildObject2())->setAttribute2('on-delete-cascade-attribute-overridden')
            );

        $objects[] = $object;

        foreach ($objects as $object) {
            $manager->persist($object);
        }
    }
}
