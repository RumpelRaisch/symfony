<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class RaischController extends Controller
{
    /**
     * @Route("/", name="raisch")
     */
    public function index()
    {
        return $this->render('raisch/index.html.twig', [
            'controller_name' => 'RaischController',
        ]);
    }
}
