<?php
/**
 * Created by PhpStorm.
 * User: f1r3starter
 * Date: 08.07.18
 * Time: 15:35
 */

namespace application;

use Brackets\Brackets;

class Application
{
    private static $messages = [
        'welcomeMessage' => "Please, enter your brackets or type '". self::EXIT_WORD . "' to quit\r\n",
        'correctBrackets' => "The string has correct brackets order.\r\n",
        'incorrectBrackets' => "The string has incorrect brackets order.\r\n",
        'exitMessage' => "Have a nice day!\r\n",
    ];

    const EXIT_WORD = 'exit';

    public function start()
    {
        $socketServer = new SocketServer();

        try {
            $socketServer->startSocket();
            while (true) {
                $socketServer->writeToFile();
                if ($socketServer->socketSelect()) {
                    continue;
                }
                if ($socketServer->acceptConnection()) {
                    $socketServer->sendMessage("HTTP/1.1 200 OK\nDate: Mon, 27 Jul 2009 12:28:53 GMT\nServer: Apache/2.2.14 (Win32)\nLast-Modified: Wed, 22 Jul 2009 19:15:56 GMT\nContent-Length: 3\nContent-Type: text/html\nConnection: Closed\n\n123");
                }
                try {
                    $data = $socketServer->readData();
                    if (trim($data) === self::EXIT_WORD) {
                        $socketServer->sendMessage(self::$messages['exitMessage']);
                        $socketServer->closeConnection();
                    } elseif ($data !== false) {
                        $socketServer->addToQueue(time(), $data);
                        $socketServer->closeConnection();
             //           $socketServer->sendMessage("HTTP/1.1 200 OK\nDate: Mon, 27 Jul 2009 12:28:53 GMT\nServer: Apache/2.2.14 (Win32)\nLast-Modified: Wed, 22 Jul 2009 19:15:56 GMT\nContent-Length: 0\nContent-Type: text/html\nConnection: Closed\n");
                    }
                } catch (\InvalidArgumentException $exception) {
                    $socketServer->sendMessage($exception->getMessage() . "\r\n");
                } catch (\Exception $exception) {
                    $socketServer->sendMessage($exception->getMessage() . "\r\n");
                }
            }
            $socketServer->closeSocket();
        } catch (NetworkException $exception) {
            echo $exception->getMessage();
        } catch (\TypeError $exception) {
            echo $exception->getMessage();
        }
    }


    /**
     * @return bool
     */
    private function proceed($data): bool
    {
        return (new Brackets($data))->isCorrect();
    }
}