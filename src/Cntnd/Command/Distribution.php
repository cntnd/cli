<?php

namespace Cntnd\Command;


use SimpleCli\Options\Help;
use SimpleCli\SimpleCli;
use z4kn4fein\SemVer\Version;
use z4kn4fein\SemVer\VersionFormatException;

/**
 * Make a new Distribution with an incremented Version.
 */
class Distribution extends AbstractCommand
{
    use Help;

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

    /**
     * @option
     *
     * Optional: Increment Major version
     */
    public bool $major = false;

    /**
     * @option
     *
     * Optional: Increment Minor version
     */
    public bool $minor = false;

    public function run(SimpleCli $cli): bool
    {
        if (!$this->hasPackageJson($cli)) {
            return false;
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
        $this->updatePackageJson($version);

        // gulp

        if (!$this->hasGulpfile($cli)) {
            return false;
        }

        $this->info($cli, "Running gulp dist");
        $gulp_dist = exec("gulp dist");
        $this->info($cli, $gulp_dist);

        // endregion

        return true;
    }

    private function versionFromPackageJson(): string
    {
        $packageJson = $this->packageJson();
        return $packageJson['version'];
    }

    /**
     * @throws VersionFormatException
     */
    private function incrementVersion($cli, $ver): string
    {
        if ($this->no_increment === true) {
            $this->info($cli, "no_increment");
        }
        $semver = Version::parse($ver);

        if ($this->major == true) {
            return (string)$semver->getNextMajorVersion();
        } else if ($this->minor == true) {
            return (string)$semver->getNextMinorVersion();
        }
        return (string)$semver->getNextPatchVersion();
    }

    private function updatePackageJson($version): void
    {
        $packageJson = $this->packageJson();
        $packageJson['version'] = $version;

        file_put_contents(
            "package.json",
            json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}