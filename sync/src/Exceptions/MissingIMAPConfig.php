<?php

namespace App\Exceptions;

class MissingIMAPConfig extends \Exception
{
    public $code = EXC_MISSING_IMAP;
    public $message = "IMAP config not found for %s.";

    public function __construct( $type )
    {
        $this->message = sprintf( $this->message, $type );
    }
}