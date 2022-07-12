<?php

namespace Cntnd\Command;


use SimpleCli\Options\Help;
use SimpleCli\Options\Verbose;
use SimpleCli\SimpleCli;

/**
 * Add new Template File to Module.
 */
class Template extends AbstractCommand
{
    use Help;
    use Verbose;

    private static string $MODULE = "_MODULE_";
    private static string $TEMPLATE = "_TEMPLATE_";

    /**
     * @argument
     *
     * The Template Name (snake case)
     *
     * @var string
     */
    public string $templateName;

    /**
     * @option
     *
     * Optional: The Module Name (snake case)
     */
    public string $module = "";

    /**
     * @option
     *
     * Optional: The destination path, default = src
     */
    public string $destination = "src";

    public function run(SimpleCli $cli): bool
    {
        $moduleName = $this->module;
        if (empty($moduleName)) {
            $moduleName = $this->moduleName($cli);
            if (empty($moduleName)) {
                return $this->error($cli, "Please enter a Module name");
            }
        }

        if (empty($this->templateName)) {
            return $this->error($cli, "Please enter a Template name");
        }

        if (!$this->ensureTemplateDirectoryExists()) {
            $path = $this->destination;
            return $this->error($cli, "Unable to find the Template directory (${path})");
        }

        // RUN

        if ($this->verbose) {
            $this->info($cli, "Generating Template $this->templateName for Module $this->moduleName");
        }

        $this->copyTemplate($cli, $moduleName);

        // endregion

        return true;
    }

    protected function copyTemplate(SimpleCli $cli, string $moduleName): void
    {
        $templates = __DIR__ . '/../template';

        $this->info($cli, "Template: ${templates}");

        foreach (scandir($templates) ?: [] as $file) {
            if (substr($file, 0, 1) !== '.') {
                $originPath = $templates . '/' . $file;
                $targetPath = $this->targetPath() . $file;
                $this->copyAndRename($cli, $originPath, $targetPath, $moduleName);
            }
        }
    }

    private function targetPath(): string
    {
        return $this->destination . '/template/';
    }

    private function copyAndRename(SimpleCli $cli, string $originPath, string $targetPath, string $moduleName): void
    {
        $path = strtr($targetPath, [
            $this::$MODULE => $moduleName,
            $this::$TEMPLATE => $this->templateName]);

        if ($this->verbose) {
            $cli->writeLine("Creating ${path}");
        }

        if (is_file($originPath)) {
            file_put_contents(
                $path,
                strtr(
                    (string)file_get_contents("$originPath"),
                    [
                        $this::$MODULE => $moduleName,
                        $this::$TEMPLATE => $this->templateName
                    ]
                )
            );
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     *
     * @return bool
     */
    protected function ensureTemplateDirectoryExists(): bool
    {
        return is_dir($this->destination);
    }
}