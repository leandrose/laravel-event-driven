<?php

namespace LeandroSe\LaravelEventDriven\Console\Commands\Traits;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use LeandroSe\LaravelEventDriven\Message;

/**
 * @method void line($string)
 */
trait PrintTrait
{

    public function print(Message $msg, string $status)
    {
        $benninger = sprintf(
            '%s %s ',
            Carbon::now()->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT),
            $msg->topic,
        );
        $end = sprintf(' %s', $status);
        $separator = '';
        $count = 80 - (strlen($benninger . $end));
        if ($count > 0) {
            $separator = str_repeat('.', $count);
        }
        $this->line($benninger . $separator . $end);
    }

    public function printSuccess(Message $msg)
    {
        $this->print($msg, 'SUCCESS');
    }

    public function printError(Message $msg)
    {
        $this->print($msg, 'ERROR');
    }

    public function printRunning(Message $msg)
    {
        $this->print($msg, 'RUNNING');
    }
}