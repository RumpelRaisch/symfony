<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Logger\LogLevel;
use App\Logger\SimpleFileLogger;

use \Exception;

/**
 * [PlaygroundController description]
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class PlaygroundController extends Abstracts\AbstractController
{
    public const CONTROLLER_NAME    = 'playground';
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
    public function indexView()
    {
        $test = [];
        $str = '200 Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = 'Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = '200';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = ' Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = '200 ';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = ' ';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str = '';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];

        $levels         = [];
        $log            = [];
        $log['env']     = getenv('LOG_LEVEL');
        $log['explode'] = explode(',', $log['env']);

        foreach ($log['explode'] as $i => $logLevel) {
            try {
                $value = constant(LogLevel::class . '::' . $logLevel);

                $levels[] = $value;

                $log['explode'][$i] = [
                    $logLevel,
                    $value,
                ];
            } catch (Exception $ex) {
                // 42
            }
        }

        $logger = new SimpleFileLogger(
            $this->get('kernel')->getLogDir() . '/app.log',
            ...$levels
        );

        return $this->render('playground/index.html.twig', [
            'config' => [
                'pageTitle'        => 'Playground',
                'activeController' => [
                    'name' => self::CONTROLLER_NAME,
                    'sub'  => self::CONTROLLER_NAME . '.index',
                ],
                'brandText'        => 'Playground',
                'brandUrl'         => $this->generateAbsoluteUrl('playground.index'),
            ] + $this->getBaseTemplateConfig(),
            'test'   => [
                $log,
                $levels,
                $logger->getFlags(),
                $this->get('kernel')->getLogDir(),
                $test,
            ],
        ]);
    }

    /**
     * @Route("/playground/icons", name="playground.icons")
     */
    public function iconsView()
    {
        return $this->render('playground/icons.html.twig', [
            'config'  => [
                'pageTitle'        => 'Icons',
                'activeController' => [
                    'name' => self::CONTROLLER_NAME,
                    'sub'  => self::CONTROLLER_NAME . '.icons',
                ],
                'brandText'        => 'Icons',
                'brandUrl'         => $this->generateAbsoluteUrl('playground.icons'),
            ] + $this->getBaseTemplateConfig(),
            'matches' => $this->parseNucleoIconsCss(),
        ]);
    }

    /**
     * @Route("/playground/photos", name="playground.photos")
     */
    public function photosView()
    {
        return $this->render('playground/photos.html.twig', [
            'config' => [
                'pageTitle'        => 'Photos',
                'activeController' => [
                    'name' => self::CONTROLLER_NAME,
                    'sub'  => self::CONTROLLER_NAME . '.photos',
                ],
                'brandText'        => 'Photos',
                'brandUrl'         => $this->generateAbsoluteUrl('playground.photos'),
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

        preg_match_all('/\.([a-zA-Z-0-9]+)::before \{/', $content, $matches, PREG_SET_ORDER);

        return $matches;
    }
}
