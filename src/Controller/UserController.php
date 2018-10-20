<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

/**
 * [UserController description]
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class UserController extends Abstracts\AbstractController
{
    /**
     * @Route("/login", name="user.login")
     */
    public function loginView()
    {
        return $this->render('user/login.html.twig', []);
    }
}
