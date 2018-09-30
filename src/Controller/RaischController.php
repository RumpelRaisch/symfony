<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;

/**
 * [RaischController description].
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class RaischController extends Controller
{
    /**
     * Response Objekt.
     *
     * @var null|Response
     */
    private $response = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->response = new Response();
        $this->response
            ->setStatusCode(200)
            ->setCharset('UTF-8')
            ->headers->set('Content-Type', 'text/plain');
    }

    /**
     * @Route("/", name="raischcontroller_index")
     */
    public function index(Request $request): Response
    {
        return $this->render('raisch/index.html.twig', [
            'title' => 'Raisch::Symfony->Test',
            'debug' => print_r($this->getDebug(['request' => $request]), true),
        ]);
    }

    /**
     * @Route("/debug", name="raischcontroller_debug")
     */
    public function debug(Request $request): Response
    {
        return JsonResponse::create($this->getDebug(['request' => $request]));
    }

    /**
     * Provides data about the request, globals, environment etc.
     *
     * @param  array $add additional data
     * @return array      combined data
     */
    private function getDebug(array $add = []): array
    {
        return array_merge([
            'php.version'     => PHP_VERSION,
            'symfony.version' => Kernel::VERSION,
            'headers'         => getallheaders(),
            '$_GET'           => $_GET,
            '$_POST'          => $_POST,
            '$_COOKIE'        => $_COOKIE,
            '$_FILES'         => $_FILES,
            '$_SERVER'        => $_SERVER,
            '$_ENV'           => $_ENV,
        ], $add);
    }
}
