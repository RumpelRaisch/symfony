<?php
namespace App\Helper;

/**
 * LoremIpsum class
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class LoremIpsumHelper
{
    private const LOREM_IPSUM_API_URI = 'http://loripsum.net/api/plaintext/short/';
    private const LOREM_IPSUM_BACKUP  = 'Lorem ipsum dolor sit amet, consectetu'
        . 'r adipiscing elit, sed do eiusmod tempor incididunt ut labore et dol'
        . 'ore magna aliqua.' . PHP_EOL;

    /**
     * Gets a placeholder text from loripsum.net.
     *
     * @param  integer $paragraphs number of paragraphs
     * @param  string  $pClass     css class for p tags
     * @param  boolean $addPTags   use p tags?
     * @param  boolean $prude      prude API call?
     *
     * @return string               lorem ipsum text
     */
    public static function get(
        int    $paragraphs = 1,
        string $pClass     = null,
        bool   $addPTags   = true,
        bool   $prude      = true
    ): string {
        $params = [];

        if (1 > $paragraphs) {
            $paragraphs = 1;
        }

        $params[] = $paragraphs;

        if (true === $prude) {
            $params[] = 'prude';
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, self::LOREM_IPSUM_API_URI . implode('/', $params));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

        $lorem = curl_exec($curl);

        curl_close($curl);

        if (false === $lorem) {
            $lorem = '';

            for ($i = 0; $i < $paragraphs; ++$i) {
                $lorem .= self::LOREM_IPSUM_BACKUP;
            }
        }

        if (true === $addPTags) {
            $css   = $pClass ? ' class="' . $pClass . '"' : '';
            $lorem = "<p{$css}>" . preg_replace(
                '#(\r\n|\n)+#',
                "</p><p{$css}>",
                trim($lorem)
            ) . '</p>';
        }

        return $lorem;
    }
}
