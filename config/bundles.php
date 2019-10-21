<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Draw\Bundle\TesterBundle\DrawTesterBundle::class => ['test' => true],
    Sonata\DatagridBundle\SonataDatagridBundle::class => ['dev' => true, 'test' => true],
    Sonata\CoreBundle\SonataCoreBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Sonata\BlockBundle\SonataBlockBundle::class => ['dev' => true, 'test' => true],
    Knp\Bundle\MenuBundle\KnpMenuBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Sonata\AdminBundle\SonataAdminBundle::class => ['all' => true],
    Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle::class => ['dev' => true, 'test' => true],
    Draw\Bundle\UserBundle\DrawUserBundle::class => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
];
