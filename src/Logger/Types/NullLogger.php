<?php
namespace App\Logger\Types;

use App\Logger\Abstracts\AbstractLogger;

/**
 * Class NullLogger
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class NullLogger extends AbstractLogger
{
    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        // dummy
    }
}
