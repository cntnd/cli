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
     * The Module Name (snake case)
     *
     * @var string
     */
    public string $moduleName;

    /**
     * @argument
     *
     * The Template Name (snake case), if Empty uses Module Name
     *
     * @var string
     */
    public string $templateName;

    /**
     * @option
     *
     * Optional: The destination path, default = src
     */
    public string $destination = "src";

    public function run(SimpleCli $cli): bool
    {
        if (empty($this->moduleName)) {
            return $this->error($cli, "Please enter a Module name");
        }

        if (empty($this->templateName)) {
            $moduleName = strtolower($this->moduleName);
            $this->templateName = $moduleName;
            $this->info($cli, "Use snake case Template Name: ${moduleName}");
        }

        if (!$this->ensureTemplateDirectoryExists()) {
            $path = $this->destination;
            return $this->error($cli, "Unable to find the Template directory (${path})");
        }

        // RUN

        if ($this->verbose) {
            $this->info($cli, "Generating Template $this->templateName for Module $this->moduleName");
        }

        $this->copyTemplate($cli);

        // endregion

        return true;
    }

    protected function copyTemplate(SimpleCli $cli): void
    {
        $templates = __DIR__ . '/../template';

        $this->info($cli, "Template: ${templates}");

        foreach (scandir($templates) ?: [] as $file) {
            if (substr($file, 0, 1) !== '.') {
                $originPath = $templates . '/' . $file;
                $targetPath = $this->targetPath() . $file;
                $this->copyAndRename($cli, $originPath, $targetPath);
            }
        }
    }

    private function targetPath(): string
    {
        return $this->destination . '/template/';
    }

    private function copyAndRename(SimpleCli $cli, string $originPath, string $targetPath): void
    {
        $path = strtr($targetPath, [
            $this::$MODULE => $this->moduleName,
            $this::$TEMPLATE => $this->templateName]);

        if ($this->verbose) {
            $cli->writeLine("Creating ${path}");
        }

        if (is_dir($originPath)) {
            mkdir($targetPath);
            foreach (scandir($originPath) ?: [] as $file) {
                if ($file != "." && $file != "..") {
                    $this->copyAndRename($cli, "$originPath/$file", strtr("$targetPath/$file", [
                        $this::$MODULE => $this->moduleName,
                        $this::$TEMPLATE => $this->templateName
                    ]));
                }
            }
        } else if (is_file($originPath)) {
            file_put_contents(
                $path,
                strtr(
                    (string)file_get_contents("$originPath"),
                    [
                        $this::$MODULE => $this->moduleName,
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