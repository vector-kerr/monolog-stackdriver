<?php

namespace Vector88\Monolog\Stackdriver;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Google\Cloud\Logging\LoggingClient;

class StackdriverHandler extends PsrHandler {

    protected $_gcl;
    protected $_logger;

    public function __construct( $projectId, $loggerName, $level = Logger::DEBUG, $bubble = true ) {
        $this->_gcl = new LoggingClient( [ 'projectId' => $projectId ] );
        $this->_logger = $this->_gcl->logger( $loggerName );
    }

    public function write( array $record ) {
        $this->_logger->write( $this->_logger->entry( $record ) );
    }

}
