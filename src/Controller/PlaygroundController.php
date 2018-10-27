<?php
namespace App\Controller;

use \Exception;
use App\Logger\LoggerContainer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PlaygroundController class
 *
 * @IsGranted("ROLE_DEV")
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class PlaygroundController extends Abstracts\AbstractController
{
    public const CONTROLLER_NAME    = 'playground';
    public const SESSION_PLAYGROUND = self::SESSION_ROOT . '/' . self::CONTROLLER_NAME;

    private $context = [];

    /**
     * PlaygroundController constructor
     *
     * @param KernelInterface $kernel
     *
     * @throws Exception
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->setDefaultSession();
        $this->context['__AREA__'] = 'PlaygroundController';

        if (null === $this->getSession()->get(self::SESSION_PLAYGROUND, null)) {
            $this->getSession()->set(self::SESSION_PLAYGROUND, []);
        }
    }

    /**
     * @Route("/playground/icons", name="playground.icons")
     */
    public function iconsView(): Response
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . '.icons', $this->context);

        return $this->render(self::CONTROLLER_NAME . '/icons.html.twig', [
            'config'  => [
                'pageTitle'        => ucfirst(self::CONTROLLER_NAME) . ' Icons',
                'activeController' => [
                    'name' => self::CONTROLLER_NAME,
                    'sub'  => self::CONTROLLER_NAME . '.icons',
                ],
                'brandText'        => ucfirst(self::CONTROLLER_NAME) . ' Icons',
                'brandUrl'         => $this->generateAbsoluteUrl(
                    self::CONTROLLER_NAME . '.icons'
                ),
            ] + $this->getBaseTemplateConfig(),
            'matches' => $this->parseNucleoIconsCss(),
        ]);
    }

    /**
     * @Route("/playground/photos", name="playground.photos")
     */
    public function photosView(): Response
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . '.photos', $this->context);

        return $this->render(self::CONTROLLER_NAME . '/photos.html.twig', [
            'config' => [
                'pageTitle'        => ucfirst(self::CONTROLLER_NAME) . ' Photos',
                'activeController' => [
                    'name' => self::CONTROLLER_NAME,
                    'sub'  => self::CONTROLLER_NAME . '.photos',
                ],
                'brandText'        => ucfirst(self::CONTROLLER_NAME) . ' Photos',
                'brandUrl'         => $this->generateAbsoluteUrl(
                    self::CONTROLLER_NAME . '.photos'
                ),
            ] + $this->getBaseTemplateConfig(),
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

        preg_match_all(
            '/\.([a-zA-Z-0-9]+)::before \{/',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        return $matches;
    }
}
