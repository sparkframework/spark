<?php

namespace Spark\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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

        if (is_dir($name)) {
            $output->writeln("<error>Application '$name' already exists</error>");
            return 1;
        }

        $directories = [
            "$name/config/environments",
            "$name/app/assets/javascripts",
            "$name/app/assets/stylesheets",
            "$name/app/assets/vendor/javascripts",
            "$name/app/assets/vendor/stylesheets",
            "$name/app/controllers",
            "$name/app/views",
            "$name/lib",
            "$name/public"
        ];

        foreach ($directories as $dir) {
            mkdir($dir, 0755, true);
        }

        file_put_contents("$name/public/index.php", $this->template("public/index.php"));
        file_put_contents("$name/config/bootstrap.php", $this->template("config/bootstrap.php"));
        file_put_contents("$name/config/routes.php", $this->template("config/routes.php"));
        file_put_contents("$name/config/environments/production.php", $this->template("config/environments/production.php"));
        file_put_contents("$name/config/environments/development.php", $this->template("config/environments/development.php"));

        file_put_contents("$name/composer.json", $this->template("composer.json"));
        file_put_contents("$name/README.txt", $this->template("README.txt"));

        file_put_contents("$name/config/application.php", $this->template("config/application.php", [
            "AppName" => Strings::camelize($name, true)
        ]));
    }

    protected function template($name, $variables = [])
    {
        $replace = array_map(function($var) {
            return "__{$var}__";
        }, array_keys($variables));

        $template = file_get_contents(__DIR__ . "/templates/$name");
        $template = str_replace($replace, array_values($variables), $template);

        return $template;
    }
}
