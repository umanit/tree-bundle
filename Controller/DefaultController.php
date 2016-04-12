<?php

namespace Umanit\Bundle\TreeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function notFoundAction(Request $request)
    {
        throw $this->createNotFoundException('Page not found');
    }
}
