<?php

namespace Vector88\Monolog\Stackdriver;

use Monolog\Logger;
use Monolog\Handler\PsrHandler;
use Psr\Log\LoggerInterface;

use Google\Cloud\Logging\LoggingClient;

//
// Updated to use a PSR handler, because it makes sense!
// As per https://stackoverflow.com/a/42103040/2369276
//
class StackdriverHandler extends PsrHandler {

    protected $_projectId;
    protected $_loggerName;
    protected $_gcl;

    /**
     * @var LoggerInterface[]
     */
    protected $loggers;

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
    }

    /**
     * Retrieve a logger by the given channel name
     * @param  string $channel The channel name
     * @return LoggerInterface The logger for the given channel
     */
    public function getLogger( $channel ) {
        if( !isset( $this->loggers[ $channel ] ) ) {
            $labels = [ 'context' => $channel ];
            $logger = $this->_gcl->psrLogger( $this->_loggerName, [ 'labels' => $labels ] );
            $this->loggers[ $channel ] = $logger;
        }

        return $this->loggers[ $channel ];
    }

    /**
     * {@inheritDoc}
     */
    public function handle( array $record ) {
        if( !$this->isHandling( $record ) ) {
            return false;
        }

        $channel = $record[ 'channel' ];
        $level = strtolower( $record[ 'level_name' ] );
        $message = $record[ 'message' ];
        $context = $record[ 'context' ];

        $logger = $this->getLogger( $channel );
        $logger->log( $level, $message, $context );

        return ( false === $this->bubble );
    }

    /**
     * Get the Project ID associated with this Stackdriver Handler
     * @return string The Project ID
     */
    public function getProjectId() {
        return $this->_projectId;
    }

    /**
     * Get the Logger Name associated with this Stackdriver Handler
     * @return string The Logger Name
     */
    public function getLoggerName() {
        return $this->_loggerName;
    }

}
