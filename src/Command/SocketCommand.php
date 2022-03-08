<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// Include ratchet libs
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

// Change the namespace according to your bundle
use App\Sockets\Notifications;

class SocketCommand extends Command
{
    protected function configure()
    {
        $this->setName('sockets:start-chat')
            // the short description shown while running "php bin/console list"
            ->setHelp("Starts the notifications server")
            // the full command description shown when running the command with
            ->setDescription('Starts the notifications server')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Notifications socket',// A line
            '============',// Another line
            'Running...',// Empty line
        ]);
        
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new Notifications()
                )
            ),
            8080
        );
        
        $server->run();
    }
}