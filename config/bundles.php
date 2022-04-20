<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Draw\Bundle\TesterBundle\DrawTesterBundle::class => ['test' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Sonata\BlockBundle\SonataBlockBundle::class => ['dev' => true, 'test' => true],
    Knp\Bundle\MenuBundle\KnpMenuBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Sonata\AdminBundle\SonataAdminBundle::class => ['all' => true],
    Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle::class => ['dev' => true, 'test' => true],
    Sonata\Form\Bridge\Symfony\SonataFormBundle::class => ['all' => true],
    Sonata\Twig\Bridge\Symfony\SonataTwigBundle::class => ['all' => true],
    Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle::class => ['all' => true],
    Draw\Bundle\PostOfficeBundle\DrawPostOfficeBundle::class => ['all' => true],
    Draw\Bundle\UserBundle\DrawUserBundle::class => ['all' => true],
    Draw\Bundle\CommandBundle\DrawCommandBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    JMS\SerializerBundle\JMSSerializerBundle::class => ['all' => true],
    Draw\Bundle\OpenApiBundle\DrawOpenApiBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Draw\Bundle\SonataExtraBundle\DrawSonataExtraBundle::class => ['all' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Scheb\TwoFactorBundle\SchebTwoFactorBundle::class => ['all' => true],
    Terminal42\ServiceAnnotationBundle\Terminal42ServiceAnnotationBundle::class => ['dev' => true, 'test' => true],
    Aws\Symfony\AwsBundle::class => ['all' => true],
    Draw\Bundle\AwsToolKitBundle\DrawAwsToolKitBundle::class => ['all' => true],
    Draw\Bundle\SonataIntegrationBundle\DrawSonataIntegrationBundle::class => ['all' => true],
    Draw\Bundle\FrameworkExtraBundle\DrawFrameworkExtraBundle::class => ['all' => true],
    Symplify\ComposerJsonManipulator\ComposerJsonManipulatorBundle::class => ['dev' => true, 'test' => true],
];
