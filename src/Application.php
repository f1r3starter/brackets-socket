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
                if ($socketServer->socketSelect()) {
                    continue;
                }
                if ($socketServer->acceptConnection()) {
                    $socketServer->sendMessage(Application::$messages['welcomeMessage']);
                }
                try {
                    $data = $socketServer->readData();
                    if (trim($data) === self::EXIT_WORD) {
                        $socketServer->sendMessage(self::$messages['exitMessage']);
                        $socketServer->closeConnection();
                    } elseif ($data !== false) {
                        $socketServer->sendMessage(self::$messages[$this->proceed($data) ?
                            'correctBrackets' :
                            'incorrectBrackets']);
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