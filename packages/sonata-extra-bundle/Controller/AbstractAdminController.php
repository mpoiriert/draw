<?php

namespace Draw\Bundle\SonataExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractAdminController extends AbstractController implements AdminControllerInterface
{
    use ControllerTrait;
}
