<?php namespace Draw\Bundle\UserBundle\Sonata\Block;

use Sonata\AdminBundle\Block\AdminStatsBlockService;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserCountBlock extends AdminStatsBlockService
{
    /**
     * @var string
     */
    private $adminCode;

    /**
     * @param string $userAdminCode
     * 
     * @required
     */
    public function setAdminCode(string $userAdminCode)
    {
        $this->adminCode = $userAdminCode;
    }

    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'icon' => 'fa-user',
            'text' => 'Total users',
            'translation_domain' => null,
            'color' => 'bg-aqua',
            'code' => $this->adminCode,
            'filters' => [],
            'limit' => 1000,
            'template' => '@SonataAdmin/Block/block_stats.html.twig',
        ]);
    }
}