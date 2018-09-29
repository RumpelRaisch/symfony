<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;

class RaischController extends Controller
{
    /**
     * @Route("/", name="raisch")
     */
    public function index(Request $request): Response
    {
        return $this->render('raisch/index.html.twig', [
            'title' => 'RS - Test',
            'debug' => print_r([
                'php.version'     => PHP_VERSION,
                'symfony.version' => Kernel::VERSION,
                'headers'         => getallheaders(),
                '$_GET'           => $_GET,
                '$_POST'          => $_POST,
                '$_COOKIE'        => $_COOKIE,
                '$_FILES'         => $_FILES,
                '$_SERVER'        => $_SERVER,
                '$_ENV'           => $_ENV,
                'request'         => $request,
            ], true),
        ]);
    }
}
