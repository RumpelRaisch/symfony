<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * [ApiController description]
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class ApiController extends Abstracts\AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSession();
    }

    /**
     * @Route("/api/gubed", name="api.gubed")
     */
    public function gubed(Request $request): JsonResponse
    {
        return JsonResponse::create($this->getDebug(['request' => $request]));
    }

    /**
     * @Route("/api/gubed/headers", name="api.gubed.headers")
     */
    public function gubedHeaders(): JsonResponse
    {
        return JsonResponse::create(getallheaders());
    }

    /**
     * @Route("/api/gubed/server", name="api.gubed.server")
     */
    public function gubedServer(): JsonResponse
    {
        return JsonResponse::create($_SERVER);
    }

    /**
     * @Route("/api/gubed/session", name="api.gubed.session")
     */
    public function gubedSession(): JsonResponse
    {
        return JsonResponse::create($_SESSION);
    }
}
