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
    protected $output;

    protected function configure()
    {
        $this->setName('create')
            ->setDescription('Creates a new application')
            ->addOption('skip-composer', null, InputOption::VALUE_NONE, "Skip installing dependencies after creating the skeleton")
            ->addOption('chdir', 'C', InputOption::VALUE_REQUIRED, "Change directory before creating the application")
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, "Create application in this directory")
            ->addArgument('application-name', InputArgument::REQUIRED, 'Name of the application');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('application-name');
        $appName = Strings::camelize(basename($name), true);

        $directory = $input->getOption('output') ?: $name;

        if ($chdir = $input->getOption('chdir')) {
            chdir($chdir);
        }

        if (is_dir($directory)) {
            $output->writeln("<error>Application '$name' already exists</error>");
            return 1;
        }

        $this->output = $output;

        mkdir($directory, 0755, true);
        chdir($directory);

        $output->writeln(sprintf(
            "-----> Creating application <info>%s</info> in %s...",
            $appName, realpath($directory)
        ));

        $directories = [
            "config/environments",
            "config/initializers",
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
            "tests/unit",
            "extra"
        ];

        foreach ($directories as $dir) {
            mkdir($dir, 0755, true);
            touch("$dir/.empty");
        }

        # Create a world writeable data directory for file caching and temporary files.
        # Available as `spark.data_directory` application variable.
        mkdir("data", 0777, true);
        touch("data/.empty");

        # Create a default Controller
        $this->createFile(
            "app/controllers/$appName/IndexController.php",
            $this->template("app/controllers/IndexController.php", ['AppName' => $appName])
        );

        $this->createFile("app/views/index/index.phtml", "<h1>Hello World</h1>");

        $this->fileFromTemplate("public/index.php");
        $this->fileFromTemplate("public/.htaccess");

        # Sample NGINX config
        $this->fileFromTemplate('extra/nginx.conf');

        # Default layout
        $this->fileFromTemplate("app/views/layouts/default.phtml");

        # Default configuration files:
        $this->fileFromTemplate("config/bootstrap.php");
        $this->fileFromTemplate("config/routes.php");
        $this->fileFromTemplate("config/pipe.php");

        # Create Environment specific config files.
        $this->fileFromTemplate("config/environments/production.php");
        $this->fileFromTemplate("config/environments/development.php");
        $this->fileFromTemplate("config/environments/testing.php");

        $this->createFile("app/assets/stylesheets/application.css", "/* This is the default stylesheet */");
        $this->createFile("app/assets/javascripts/application.js", "/* This is the default javascript file */");

        $this->fileFromTemplate('tests/bootstrap.php');
        $this->fileFromTemplate('phpunit.dist.xml');

        $this->fileFromTemplate('bob_config.php');
        $this->fileFromTemplate('composer.json');
        $this->fileFromTemplate('README.txt');

        $this->createFile('.gitignore', join("\n", [
            "/vendor/",
            "/public/assets/",
            "/composer.phar",
            "/data/app.log"
        ]));

        # Store the current application skeleton version, for later upgrades using
        # the `upgrade` command.
        file_put_contents('.spark_version', \Spark\Spark::VERSION);
        file_put_contents('.app_version', \Spark\Spark::SKELETON_VERSION);

        $this->fileFromTemplate('config/application.php', ['AppName' => $appName]);

        $output->writeln("-----> Downloading Composer...");
        $this->downloadComposer();

        if (!$input->getOption('skip-composer')) {
            $output->writeln("-----> Installing Application Dependencies");
            passthru('php composer.phar install --dev');
        }

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
        $this->output->writeln("<info>Creating</info> $file");
        file_put_contents($file, $this->template($file, $variables));
    }

    protected function createFile($file, $content = null)
    {
        $this->output->writeln("<info>Creating</info> $file");
        file_put_contents($file, $content ?: "");
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
