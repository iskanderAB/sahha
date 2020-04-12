<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class PasswordController extends AbstractController {

    /**
     * @Route("/api/resetpassword",name="reset_password",methods={"POST"})
     */
    public function sendEmail (Request $request){
//        $transport = new GmailSmtpTransport($_ENV['EMAIL'], $_ENV['PASSWORD']);
//        $mailer = new Mailer($transport);
//        $email = new Email();
//        $email->subject('test');
//        $email->addTo('mou3in02@gmail.com');
//        $email->from('pfepfe32@gmail.com');
//        $email->text('hello');
//        $mailer->send($email);
        return new Response('ok');
    }
}