<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * [RaischController description].
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class RaischController extends Abstracts\AbstractController
{
    /**
     * Response Objekt.
     *
     * @var Response
     */
    private $response = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @Route("/", name="raisch.index")
     */
    public function index(Request $request): Response
    {
        return $this->render('raisch/index.html.twig', [
            'title'  => 'Raisch::Symfony->Test',
            'output' => '.',
        ]);
    }

    private function prepareDefaultResponse(): Response
    {
        $this->response = null;
        $this->response = new Response();
        $this->response
            ->setStatusCode(200)
            ->setCharset('UTF-8')
            ->headers->set('Content-Type', 'text/plain');

        return $this->response;
    }
}
