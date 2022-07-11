<?php

namespace Cntnd\Command;


use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\SimpleCli;

/**
 * Initialize the Module.
 */
class Init implements Command
{
    use Help;

    private static string $FILE = "skeleton";
    private static string $MODULE = "SKELETON";
    private static string $PACKAGE = "Skeleton";

    /**
     * @argument
     *
     * The Module Name (snake case)
     *
     * @var string
     */
    public string $moduleName;

    /**
     * @option
     *
     * Optional: Also run gulp init
     */
    public bool $gulp = false;

    public function run(SimpleCli $cli): bool
    {
        if (empty($this->moduleName)) {
            return $this->error($cli, "Please enter a Module name");
        }

        // RUN

        $moduleName = $this->moduleName;
        $this->info($cli, "Initializing Module: ${moduleName}");

        // package.json
        if (!is_file("package.json")) {
            return $this->error($cli, "Unable to find package.json");
        }
        $this->doPackageJson();

        // src files

        if ($this->gulp) {
            if (!is_file("gulpfile.js")) {
                return $this->error($cli, "Unable to find gulpfile");
            }

            $this->info($cli, "Running gulp init");
            $gulp_init = exec("gulp init");
            $this->info($cli, $gulp_init);
        }

        // endregion

        return true;
    }

    protected function doPackageJson(): void
    {
        $packageJson = "package.json";
        file_put_contents(
            $packageJson,
            strtr(
                (string)file_get_contents("$packageJson"),
                [
                    $this::$MODULE => $this->moduleName,
                    $this::$PACKAGE => $this->packageName()
                ]
            )
        );
    }

    private function packageName(): string
    {
        // Remove underscores, capitalize words.
        return ucwords(str_replace('_', ' ', $this->moduleName));
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
}