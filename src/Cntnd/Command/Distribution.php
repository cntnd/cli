<?php

namespace Cntnd\Command;


use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\Options\Verbose;
use SimpleCli\SimpleCli;
use z4kn4fein\SemVer\Version;
use z4kn4fein\SemVer\VersionFormatException;

/**
 * Initialize the Module.
 */
class Distribution implements Command
{
    use Help;
    use Verbose;

    private static string $DEFAULT_VERSION = "1.0.0";

    /**
     * @option
     *
     * Optional: Force version
     */
    public string $version = "";

    /**
     * @option
     *
     * Optional: Do not increment version
     */
    public bool $no_increment = false;

    public function run(SimpleCli $cli): bool
    {
        if (!is_file("package.json")) {
            return $this->error($cli, "Unable to find package.json");
        }

        // RUN

        $increment = !$this->no_increment;
        $forced = !empty($this->version);

        $version = $this->versionFromPackageJson();
        $this->info($cli, "Version in package.json: ${version}");

        if ($increment && !$forced) {
            try {
                $version = $this->incrementVersion($cli, $version);
                $this->verbose($cli, "Incremented Version: ${version}");
            } catch (VersionFormatException $e) {
                $this->error($cli, "Invalid Version '${version}' using default '" . $this::$DEFAULT_VERSION . "'");
            }
        }

        if ($forced) {
            $version = $this->version;
            $this->verbose($cli, "Using Forced Version: ${version}");
        }

        $this->info($cli, "Version: ${version}");

        if (!is_file("gulpfile.js")) {
            return $this->error($cli, "Unable to find gulpfile");
        }

        $this->info($cli, "Running gulp dist");
        $gulp_dist = exec("gulp dist");
        $this->info($cli, $gulp_dist);

        // endregion

        return true;
    }

    private function versionFromPackageJson(): string
    {
        $packageJson = json_decode(file_get_contents("package.json"), true);

        return $packageJson['version'];
    }

    /**
     * @throws \z4kn4fein\SemVer\VersionFormatException
     */
    private function incrementVersion($cli, $ver): string
    {
        if ($this->no_increment === true) {
            $this->info($cli, "no_increment");
        }
        $semver = Version::parse($ver);
        // todo
        return (string)$semver->getNextPatchVersion();
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