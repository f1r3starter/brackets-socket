<?php
/**
 * Created by PhpStorm.
 * User: f1r3starter
 * Date: 08.07.18
 * Time: 15:36
 */

namespace application;

use brackets\Brackets;

class SocketServer
{
    private $address;
    private $port;
    private $backlog;
    private $socket;
    private $connection;
    private $clients = [];
    private $read;
    private $write = null;
    private $except = null;
    private $writeSocket;

    public function __construct($address = '127.0.0.1', $port = 1234, $backlog = 5)
    {
        $this->address = $address;
        $this->port = $port;
        $this->backlog = $backlog;
    }

    public function startSocket()
    {
        $this->createSocket()->bindSocket()->listenSocket();
    }

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

    protected function createSocket()
    {
        if (($this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
           throw new NetworkException('socket_create() failed: reason: ' . socket_strerror(socket_last_error()) . "\n");
        }
        return $this;
    }

    protected function bindSocket()
    {
        $this->isResource($this->socket);
        if (socket_bind($this->socket, $this->address, $this->port) === false) {
            throw new NetworkException('socket_bind() failed: reason: ' . socket_strerror(socket_last_error($this->socket)) . "\n");
        }
        return $this;
    }

    protected function listenSocket()
    {
        $this->isResource($this->socket);
        if (socket_listen($this->socket, 5) === false) {
            throw new NetworkException( "socket_listen() failed: reason: " . socket_strerror(socket_last_error($this->socket)) . "\n");
        }
        $this->clients = [$this->socket];
        return $this;
    }

    public function acceptConnection()
    {
        $this->isResource($this->socket);
        if (in_array($this->connection, $this->read)) {
            if (($this->clients[] = $this->connection = socket_accept($this->socket)) === false) {
                throw new NetworkException('socket_accept() failed: reason: ' . socket_strerror(socket_last_error($this->socket)) . "\n");
            }
            $key = array_search($this->socket, $this->read);
            unset($this->read, $key);
        }
        return $this;
    }

    public function sendMessage($message)
    {
        $this->isResource($this->connection);
        socket_write($this->connection, $message, strlen($message));
        return $this;
    }

    public function socketSelect()
    {
        $this->read = $this->clients;
        $this->write = null;
        $this->except = null;
        return socket_select($read, $write, $except, 0) < 1;
    }

    public function proceedBrackets(Brackets $brackets)
    {
        $this->isResource($this->connection);
        $data = false;
        foreach ($this->read as $read) {
            $data = @socket_read($read, 4096, PHP_BINARY_READ);
            if ($data === false) {
                $key = array_search($read, $this->clients);
                unset($this->clients[$key]);
                continue;
            }
            $brackets->setStr($data);
            $result = Application::$messages[$brackets->isCorrect() ?
                'correctBrackets' :
                'incorrectBrackets'];
            socket_write($read, $result);
        }
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