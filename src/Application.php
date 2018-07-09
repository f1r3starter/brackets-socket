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
        'welcomeMessage' => "Please, enter your brackets \r\n",
        'correctBrackets' => "The string has correct brackets order.\r\n",
        'incorrectBrackets' => "The string has incorrect brackets order.\r\n"
    ];

    public function start()
    {
        $socketServer = new SocketServer();
        try {
            $socketServer->startSocket();
            $socketServer->acceptConnection();
            $socketServer->sendMessage(self::$messages['welcomeMessage']);
            $data = $socketServer->readMessage();
            try {
                $socketServer->sendMessage(self::$messages[$this->proceed($data) ?
                    'correctBrackets' :
                    'incorrectBrackets']);
            } catch (\InvalidArgumentException $exception) {
                $socketServer->sendMessage($exception->getMessage() . "\r\n");
            } catch (\Exception $exception) {
                $socketServer->sendMessage($exception->getMessage() . "\r\n");
            }
            $socketServer->closeConnection();
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