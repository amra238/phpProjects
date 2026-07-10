<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class ContactCintrolller
{
    public function hello(): Response
    {
        return new Response('example@mail.ru');
    }
}
