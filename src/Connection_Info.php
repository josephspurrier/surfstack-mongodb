<?php
/**
 * MongoDB connection information
 *
 * @package SurfStack
 * @copyright Copyright (C) 2014 Joseph Spurrier. All rights reserved.
 * @author Joseph Spurrier (http://www.josephspurrier.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0.html
 */

namespace SurfStack\MongoDB;

class Connection_Info
{
    private $port = \Mongo::DEFAULT_PORT;
    private $host = '127.0.0.1';

    function __construct($host=false, $port=false)
    {
        $this->host = ($host ? $host : $this->host);
        $this->port = ($port ? $port : $this->port);
    }

    function getConnectionString()
    {
        return 'mongodb://'.$this->host.':'.$this->port;
    }
}