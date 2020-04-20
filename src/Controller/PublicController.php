<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PublicController extends AbstractController
{
    /**
     * @Route("/", name="public")
     */
    public function index()
    {
        return $this->render('public/index.html.twig', [
            'controller_name' => 'PublicController',
        ]);
    }
     /**
     * @Route("/404", name="error404")
     */
    public function index404()
    {
        return $this->render('error404/index.html.twig', [
            'controller_name' => 'PublicController',
        ]);
    }
}
