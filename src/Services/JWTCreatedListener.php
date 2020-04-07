<?php


namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();
        $payload = $event->getData();
        unset($payload["username"]);
        $payload['roles'] = $user->getRoles();
        $payload['email'] = $user->getUsername();
        $event->setData($payload);
    }
}