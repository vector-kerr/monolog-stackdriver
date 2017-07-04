<?php

namespace Vector88\Monolog\Stackdriver;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Google\Cloud\Logging\LoggingClient;

class StackdriverHandler extends AbstractProcessingHandler {

    protected $_projectId;
    protected $_loggerName;
    protected $_gcl;
    protected $_logger;

    /**
     * {@inheritDoc}
     *
     * @param string  $projectId  Google Logging Project ID
     * @param string  $loggerName Google Logging Logger Name
     */
    public function __construct( $projectId, $loggerName, $level = Logger::DEBUG, $bubble = true ) {
        parent::__construct( $level, $bubble );
        $this->_initGoogleLogger( $projectId, $loggerName );
    }

    protected function _initGoogleLogger( $projectId, $loggerName ) {
        $this->_projectId = $projectId;
        $this->_loggerName = $loggerName;
        $this->_gcl = new LoggingClient( [ 'projectId' => $this->_projectId ] );
        $this->_logger = $this->_gcl->logger( $this->_loggerName );
    }

    /**
     * {@inheritDoc}
     */
    public function write( array $record ) {
        // $message = $record[ 'message' ];
        // $context = $record[ 'context' ];
        $this->_logger->write( $this->_logger->entry( $record ) );
    }

    public function getProjectId() {
        return $this->_projectId;
    }

    public function getLoggerName() {
        return $this->_loggerName;
    }

}
