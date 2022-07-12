<?php

namespace Cntnd\Command;


use SimpleCli\Command;
use SimpleCli\Options\Verbose;
use SimpleCli\SimpleCli;

/**
 * todo
 * - check for what else?
 */

abstract class AbstractCommand implements Command
{
    use Verbose;

    /**
     * @param SimpleCli $cli
     *
     * @return bool
     */
    protected function hasPackageJson(SimpleCli $cli): bool
    {
        if (!is_file("package.json")) {
            return $this->error($cli, "Unable to find package.json");
        }
        return true;
    }

    /**
     * @param SimpleCli $cli
     *
     * @return bool
     */
    protected function hasGulpfile(SimpleCli $cli): bool
    {
        if (!is_file("gulpfile.js")) {
            return $this->error($cli, "Unable to find gulpfile.js");
        }
        return true;
    }

    /**
     * @return array
     */
    protected function packageJson(): array
    {
        return json_decode(file_get_contents("package.json"), true);
    }

    /**
     * @param SimpleCli $cli
     * @param string $text
     *
     * @return bool
     */
    protected function error(SimpleCli $cli, string $text): bool
    {
        $cli->writeLine($text, 'red');
        return false;
    }

    /**
     * @param SimpleCli $cli
     * @param string $text
     */
    protected function info(SimpleCli $cli, string $text): void
    {
        $cli->writeLine($text, 'light_cyan');
    }

    /**
     * @param SimpleCli $cli
     * @param string $text
     */
    protected function verbose(SimpleCli $cli, string $text): void
    {
        if ($this->verbose) {
            $cli->writeLine($text, 'light_cyan');
        }
    }
}