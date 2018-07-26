<?php

namespace App\Application\Connection;

use App\Application\Connection\Exception\ConnectionNotFoundException;

class ConnectionFactory
{
    /**
     * Factory method for dynamic load of classes.
     * Uses a convention-over-configuration approach: requested connection type must match with class name
     * like "Curl" as "CurlConnection"
     *
     * Returns instance on match, throws exception if no match is found
     *
     * @param string $connectionType
     * @return Connection
     * @throws ConnectionNotFoundException
     */
    public function getConnection(string $connectionType) :Connection
    {
        $connectionType = ucfirst(strtolower($connectionType));

        $fqcn = __NAMESPACE__.'\\ConcreteConnection\\'.$connectionType.'Connection';

        if (!class_exists($fqcn)) {
            throw new ConnectionNotFoundException(
                'Unable to find connection class. Looking for class '.$fqcn
            );
        };

        return new $fqcn();
    }
}