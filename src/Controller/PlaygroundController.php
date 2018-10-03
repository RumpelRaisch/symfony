<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * [PlaygroundController description]
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class PlaygroundController extends Abstracts\AbstractController
{
    public const SESSION_PLAYGROUND = self::SESSION_ROOT . '/playground';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSession();

        if (null === $this->getSession()->get(self::SESSION_PLAYGROUND, null)) {
            $this->getSession()->set(self::SESSION_PLAYGROUND, []);
        }
    }

    /**
     * @Route("/playground", name="playground.index")
     */
    public function index()
    {
        return $this->render('playground/index.html.twig', [
            'title'      => 'Playground',
            'controller' => 'playground',
            'brandText'  => 'Playground',
            'brandUrl'   => $this->generateAbsoluteUrl('playground.index'),
            'matches'    => $this->parseNucleoIconsCss(),
        ]);
    }

    /**
     * [parseNucleoIconsCss description]
     *
     * @return array [description]
     */
    private function parseNucleoIconsCss(): array
    {
        $file    = __DIR__ . '/../../public/css/nucleo-icons.css';
        $content = file_get_contents($file);
        $matches = [];

        preg_match_all('/\.([a-zA-Z-0-9]+)::before \{/', $content, $matches, PREG_SET_ORDER);

        return $matches;
    }
}
