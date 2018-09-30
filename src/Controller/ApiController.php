<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends Abstracts\AbstractController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @Route("/api/gubed", name="api.gubed")
     */
    public function gubed(Request $request): JsonResponse
    {
        return JsonResponse::create($this->getDebug(['request' => $request]));
    }
}
