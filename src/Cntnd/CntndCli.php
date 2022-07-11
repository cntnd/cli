<?php

namespace Cntnd;

use SimpleCli\SimpleCli;

class CntndCli extends SimpleCli
{
    public function getCommands(): array
    {
        return []; // Your class needs to implement the getCommands(), we'll see later what to put in here.
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }
}