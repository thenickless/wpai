<?php
namespace BK\WPAI\Common;

defined('ABSPATH') || exit;

class CustomException extends \Exception
{
    public function __construct($message = "", $code = 0, CustomException $previous = null)
    {
        parent::__construct($message, $code, $previous);

        do_action('bk.log.error', ['plugin' => 'wp-ai', 'wp-error' => $message]);
    }
}
