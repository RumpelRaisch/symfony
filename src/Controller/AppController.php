<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Helper\LoremIpsumHelper;

/**
 * [AppController description].
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class AppController extends Abstracts\AbstractController
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

        $this->setDefaultSession();
    }

    /**
     * @Route("/", name="raisch.index")
     */
    public function index(Request $request): Response
    {
        return $this->render('app/index.html.twig', [
            'title'  => 'App::Symfony->Test',
            'lorem'  => new LoremIpsumHelper(),
            'output' => '',
        ]);
    }

    private function prepareDefaultResponse(): Response
    {
        $this->response = null;
        $this->response = new Response();
        $this->response
            ->setStatusCode(Response::HTTP_OK)
            ->setCharset('UTF-8')
            ->headers->set('Content-Type', 'text/plain');

        return $this->response;
    }
}
