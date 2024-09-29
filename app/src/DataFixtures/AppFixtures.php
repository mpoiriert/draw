<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\ChildObject2;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Entity\UserTag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Component\CronJob\Entity\CronJob;
use Draw\DoctrineExtra\Common\DataFixtures\ObjectReferenceTrait;

class AppFixtures extends Fixture
{
    use ObjectReferenceTrait;

    public function load(ObjectManager $manager): void
    {
        $this->persistAndFlush(
            $manager,
            $this->loadTags()
        );

        $this->persistAndFlush(
            $manager,
            $this->loadUsers()
        );

        $this->persistAndFlush(
            $manager,
            $this->loadChildObject2()
        );

        $this->persistAndFlush(
            $manager,
            [
                (new Config())
                    ->setId('acme_demo')
                    ->setValue(['enabled' => false, 'limit' => 10]),
            ]
        );

        $this->persistAndFlush(
            $manager,
            [
                (new CronJob())
                    ->setName('test')
                    ->setCommand('echo "test"'),
            ]
        );
    }

    private function loadTags(): iterable
    {
        yield 'admin' => (new Tag())
            ->setName('admin')
            ->translate('en')
                ->setLabel('Admin')
            ->getTranslatable()
            ->translate('fr')
                ->setLabel('Administrateur')
            ->getTranslatable()
        ;

        yield 'inactive' => (new Tag())
            ->setName('inactive')
            ->setActive(false)
            ->translate('en')
                ->setLabel('Inactive')
            ->getTranslatable()
            ->translate('fr')
                ->setLabel('Inactif')
            ->getTranslatable()
        ;

        yield 'not-use' => (new Tag())
            ->setName('not-use')
            ->translate('en')
                ->setLabel('NotUse')
            ->getTranslatable()
            ->translate('fr')
                ->setLabel('Non Utilisé')
            ->getTranslatable()
        ;
    }

    private function loadUsers(): iterable
    {
        yield 'admin' => (new User())
            ->setEmail('admin@example.com')
            ->setPlainPassword('admin')
            ->setLevel(User::LEVEL_ADMIN)
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setTags([$this->getObjectReference(Tag::class, 'admin')])
            ->addUserTag(
                (new UserTag())
                    ->setTag($this->getObjectReference(Tag::class, 'admin'))
            )
            ->setAddress(
                (new Address())
                    ->setStreet('200 Acme')
            )
            ->addUserAddress(
                (new UserAddress())
                    ->setAddress(
                        (new Address())
                            ->setStreet('201 Secondary Acme')
                    )
            )
        ;

        yield (new User())
            ->setEmail('2fa-admin@example.com')
            ->setPlainPassword('2fa-admin')
            ->setLevel(User::LEVEL_ADMIN)
            ->setRoles(['ROLE_2FA_ADMIN'])
        ;

        yield (new User())
            ->setEmail('need-change-password@example.com')
            ->setNeedChangePassword(true)
            ->setLevel(User::LEVEL_ADMIN)
            ->setRoles(['ROLE_ADMIN'])
        ;

        yield (new User())
            ->setEmail('locked@example.com')
            ->setPlainPassword('locked')
            ->setLevel(User::LEVEL_ADMIN)
            ->setRoles(['ROLE_ADMIN'])
            ->setManualLock(true)
        ;

        $inactiveTag = $this->getObjectReference(Tag::class, 'inactive');

        foreach (range(1, 4) as $number) {
            $user = (new User())
                ->setEmail('user-'.str_pad((string) $number, 4, '0', \STR_PAD_LEFT).'@example.com')
                ->setPlainPassword('password')
            ;

            if (1 === $number) {
                $user
                    ->setTags([$inactiveTag])
                    ->setPreferredLocale('fr')
                ;
            }

            yield $user;
        }
    }

    private function loadChildObject2(): iterable
    {
        $user = $this->getObjectReference(User::class, 'admin');

        $objects = [];

        $user
            ->setOnDeleteRestrict(
                $objects[] = (new ChildObject2())->setAttribute2('on-delete-restrict')
            )
        ;

        $user
            ->setOnDeleteCascade(
                $objects[] = (new ChildObject2())->setAttribute2('on-delete-cascade')
            )
        ;

        $user
            ->setOnDeleteSetNull(
                $objects[] = (new ChildObject2())->setAttribute2('on-delete-set-null')
            )
        ;

        $user
            ->setOnDeleteCascadeConfigOverridden(
                $objects[] = (new ChildObject2())->setAttribute2('on-delete-cascade-config-overridden')
            )
        ;

        $user
            ->setOnDeleteCascadeAttributeOverridden(
                $objects[] = (new ChildObject2())->setAttribute2('on-delete-cascade-attribute-overridden')
            )
        ;

        yield from $objects;
    }
}
