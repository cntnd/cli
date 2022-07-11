<?php

namespace Cntnd;

use Cntnd\Command\Template;
use Cntnd\Command\Distribution;
use Cntnd\Command\Init;
use SimpleCli\SimpleCli;

class CntndCli extends SimpleCli
{
    public function getCommands(): array
    {
        return [
            'dist' => Distribution::class,
            'init' => Init::class,
            'template' => Template::class,
        ];
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }
}