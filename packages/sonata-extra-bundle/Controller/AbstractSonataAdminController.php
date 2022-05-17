<?php

namespace Draw\Bundle\SonataExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractSonataAdminController extends AbstractController implements AdminControllerInterface
{
    use ControllerTrait;
}
