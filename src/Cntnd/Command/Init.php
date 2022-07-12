<?php

namespace Cntnd\Command;


use SimpleCli\Options\Help;
use SimpleCli\SimpleCli;

/**
 * Initialize the Module.
 */
class Init extends AbstractCommand
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
     * Optional: The destination path, default = src
     */
    public string $destination = "src";

    /**
     * @option
     *
     * Optional: Folders to exclude, separated by comma
     */
    public string $folders = "";

    /**
     * @option
     *
     * Optional: Also rename info.xml
     */
    public bool $infoxml = false;

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
        if (!$this->hasPackageJson($cli)) {
            return false;
        }
        $this->updatePackageJson();

        // src files
        if (!$this->ensureModuleDirectoryExists($cli)) {
            return false;
        }
        $this->updateFiles($cli, $this->destination);

        //gulp
        if ($this->gulp) {
            if (!$this->hasGulpfile($cli)) {
                return false;
            }

            $this->verbose($cli, "Running gulp init");
            $gulp_init = exec("gulp init");
            $this->info($cli, $gulp_init);
        }

        // endregion

        return true;
    }

    protected function updatePackageJson(): void
    {
        $packageJson = "package.json";
        $this->updateFile($packageJson);
    }

    protected function updateFiles(SimpleCli $cli, $destination): void
    {
        $this->verbose($cli, "Folder: ${destination}");

        foreach (scandir($destination) ?: [] as $file) {
            if (substr($file, 0, 1) !== '.') {
                $path = $destination . '/' . $file;
                if (is_dir($path) && !$this->isExcludedFolders($cli, $file)) {
                    $this->updateFiles($cli, $path);
                } else if (is_file($path)) {
                    if ($file != "info.xml" || $this->infoxml) {
                        $this->updateFile($path);
                        $this->renameFile($cli, $destination, $file);
                    }
                }
            }
        }
    }

    private function renameFile(SimpleCli $cli, $destination, $file): void
    {
        $path = $destination . '/' . $file;
        $new = $destination . '/' . strtr(
                $file,
                [
                    $this::$FILE => $this->moduleName
                ]
            );
        rename($path, $new);
        $this->verbose($cli, "File: ${path} - new name: ${new}");
    }

    protected function updateFile($file): void
    {
        file_put_contents(
            $file,
            strtr(
                (string)file_get_contents("$file"),
                [
                    $this::$MODULE => $this->moduleName,
                    $this::$PACKAGE => $this->packageName()
                ]
            )
        );
    }

    private function isExcludedFolders(SimpleCli $cli, $folder): bool
    {
        if (!empty($this->folders)) {
            $folders = explode(",", trim($this->folders));
            $this->verbose($cli, "Has excluded Folders: ".json_encode($folders));
            return in_array($folder, $folders);
        }
        return false;
    }

    private function packageName(): string
    {
        // Remove underscores, capitalize words.
        return ucwords(str_replace('_', ' ', $this->moduleName));
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     *
     * @return bool
     */
    protected function ensureModuleDirectoryExists(SimpleCli $cli): bool
    {
        if (!is_dir($this->destination)) {
            return $this->error($cli, "Unable to find Module directory");
        }
        return true;
    }
}