<?php


namespace App\Services;


use Symfony\Component\HttpFoundation\Request;

class TokenDecoder
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getJWTToken(){
        $headers = $this->request->headers->get("authorization");
        preg_match('/Bearer\s(\S+)/',$headers,$matches);
        $token =  $matches[1];
        return $tokenParts = explode(".", $token);

    }

    public function getHeader(){
        $tokenHeader = $this->getJWTToken()[0];
        $header = base64_decode($tokenHeader);
        return json_decode($header,true);
    }

    public function getTokenType(){
        return $this->getHeader()["typ"];
    }

    public function getTokenAlgorithm(){
        return $this->getHeader()["alg"];
    }

    public function getPayload(){
        $token = $this->getJWTToken()[1];
        $payload = base64_decode($token);
        return json_decode($payload,true);
    }

    public function getIssuedAt(){
        return $this->getPayload()["iat"];
    }

    public function getExpirationTime(){
        return $this->getPayload()["exp"];
    }

    public function getEmail(){
        return $this->getPayload()["email"];
    }

    public function getRoles(){
        return $this->getPayload()["roles"];
    }
}