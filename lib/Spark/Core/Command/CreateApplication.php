<?php

namespace Spark\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use CHH\FileUtils\Path;
use Spark\Support\Strings;

class CreateApplication extends Command
{
    protected function configure()
    {
        $this->setName('create')
            ->setDescription('Creates a new application')
            ->addArgument('application_name', InputArgument::REQUIRED, 'Name of the application');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('application_name');
        $appName = Strings::camelize(basename($name), true);

        if (is_dir($name)) {
            $output->writeln("<error>Application '$name' already exists</error>");
            return 1;
        }

        $output->writeln("Creating application <info>$appName</info>...");

        mkdir($name, 0755, true);
        chdir($name);

        $directories = [
            "config/environments",
            "app/assets/javascripts",
            "app/assets/stylesheets",
            "app/assets/vendor/javascripts",
            "app/assets/vendor/stylesheets",
            "app/controllers/$appName",
            "app/views/layouts",
            "app/views/index",
            "lib",
            "public",
            "tests/",
            "tests/integration",
            "tests/unit"
        ];

        foreach ($directories as $dir) {
            mkdir($dir, 0755, true);
        }

        # Create a world writeable data directory for file caching and temporary files.
        # Available as `spark.data_directory` application variable.
        mkdir("data", 0777, true);

        # Create a default Controller
        file_put_contents(
            "app/controllers/$appName/IndexController.php",
            $this->template("app/controllers/IndexController.php", ['AppName' => $appName])
        );

        file_put_contents("app/views/index/index.phtml", "<h1>Hello World</h1>");

        $this->fileFromTemplate("public/index.php");
        $this->fileFromTemplate("app/views/layouts/default.phtml");

        $this->fileFromTemplate("config/bootstrap.php");
        $this->fileFromTemplate("config/routes.php");
        $this->fileFromTemplate("config/pipe.php");

        # Create Environment specific config files.
        $this->fileFromTemplate("config/environments/production.php");
        $this->fileFromTemplate("config/environments/development.php");

        file_put_contents("app/assets/stylesheets/application.css", "");
        file_put_contents("app/assets/javascripts/application.js", "");

        $this->fileFromTemplate('tests/bootstrap.php');

        $this->fileFromTemplate('phpunit.dist.xml');

        $this->fileFromTemplate('bob_config.php');
        $this->fileFromTemplate('composer.json');
        $this->fileFromTemplate('README.txt');

        file_put_contents('.gitignore', join("\n", [
            "/vendor/",
            "/public/assets/",
            "/composer.phar"
        ]));

        # Store the current application skeleton version, for later upgrades using
        # the `upgrade` command.
        file_put_contents('.spark_version', \Spark\Spark::VERSION);
        file_put_contents('.app_version', \Spark\Spark::SKELETON_VERSION);

        $this->fileFromTemplate('config/application.php', ['AppName' => $appName]);

        $output->writeln("Downloading Composer...");
        $this->downloadComposer();

        passthru('php composer.phar install --dev');

        $output->writeln("<info>Successfully created application $appName in " . getcwd() . "</info>");
    }

    protected function downloadComposer()
    {
        $composer = fopen("http://getcomposer.org/composer.phar", "rb");
        $out = fopen('composer.phar', 'w+');

        stream_copy_to_stream($composer, $out);
    }

    protected function fileFromTemplate($file, $variables = [])
    {
        file_put_contents($file, $this->template($file, $variables));
    }

    protected function template($name, $variables = [])
    {
        $replace = array_map(function($var) {
            return "__{$var}__";
        }, array_keys($variables));

        $templateDir = __DIR__ . '/../../../../res';

        if (!is_file("$templateDir/templates/$name")) {
            throw new \InvalidArgumentException("Template '$name' not found.");
        }

        $template = file_get_contents("$templateDir/templates/$name");
        $template = str_replace($replace, array_values($variables), $template);

        return $template;
    }
}
