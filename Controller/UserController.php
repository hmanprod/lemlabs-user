<?php

namespace LemLabs\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('LemLabsUserBundle:Default:index.html.twig', array('name' => $name));
    }
}
