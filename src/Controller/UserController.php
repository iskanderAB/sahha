<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/api/add/user")
     */
    public function addUser(Request $request) : Response{

        $response = new Response();
        $data = $request->getContent();
        dd($data);

        return $response;
    }
    // hello mouin 
}