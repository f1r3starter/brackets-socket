<?php
/**
 * Created by PhpStorm.
 * User: f1r3starter
 * Date: 08.07.18
 * Time: 15:36
 */

namespace application;

class SocketServer
{
    private $address = '127.0.0.1';
    private $port = 1234;
    private $backlog = 5;
    private $socket;
    private $connection;

    public function setBacklog($backlog)
    {
        $this->backlog = $backlog;
    }

    public function getBacklog()
    {
        return $this->backlog;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getAddress()
    {
        return $this->address;
    }

    private function isResource($resource)
    {
        if (!is_resource($resource)) {
            throw new \TypeError('Create socket first');
        }
    }

    public function createSocket()
    {
        if (($this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
           throw new NetworkException('socket_create() failed: reason: ' . socket_strerror(socket_last_error()) . "\n");
        }
        return $this;
    }

    public function bindSocket()
    {
        $this->isResource($this->socket);
        if (socket_bind($this->socket, $this->address, $this->port) === false) {
            throw new NetworkException('socket_bind() failed: reason: ' . socket_strerror(socket_last_error($this->socket)) . "\n");
        }
        return $this;
    }

    public function listenSocket()
    {
        $this->isResource($this->socket);
        if (socket_listen($this->socket, 5) === false) {
            throw new NetworkException( "socket_listen() failed: reason: " . socket_strerror(socket_last_error($this->socket)) . "\n");
        }
        return $this;
    }

    public function acceptConnection()
    {
        $this->isResource($this->socket);
        if (($this->connection = socket_accept($this->socket)) === false) {
            throw new NetworkException( 'socket_accept() failed: reason: ' . socket_strerror(socket_last_error($this->socket)) . "\n");
        }
        return $this;
    }

    public function sendMessage($message)
    {
        $this->isResource($this->connection);
        socket_write($this->connection, $message, strlen($message));
        return $this;
    }

    public function readMessage()
    {
        $this->isResource($this->connection);
        return socket_read($this->connection, 2048, PHP_NORMAL_READ);
    }

    public function closeConnection()
    {
        socket_close($this->connection);
        return $this;
    }

    public function closeSocket()
    {
        socket_close($this->socket);
        return $this;
    }
}