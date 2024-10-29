<?php

namespace Affilizz\Exception\Missing;

class Channel extends \Exception {
    /**
     * Redefines the exception for the message to not be optional.
     * @param $message String The exception message.
     * @param $code Integer The exception code.
     * @author Affilizz <wordpress@affilizz.com>
     * @return void
     */
    public function __construct( $message, $code = 0, \Throwable $previous = null ) {
        // Make sure everything is assigned properly
        parent::__construct( $message, $code, $previous );
    }

    /**
     * Returns a custom string representation of the exception object.
     * @author Affilizz <wordpress@affilizz.com>
     * @return String The string representation of the exception object.
     */
    public function __toString() {
        return __CLASS__ . ': [ ' . $this->code . '] ' . $this->message . '' . "\n";
    }
}