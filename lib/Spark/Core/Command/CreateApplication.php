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
        $appName = Strings::camelize($name);

        if (is_dir($name)) {
            $output->writeln("<error>Application '$name' already exists</error>");
            return 1;
        }

        mkdir($name, 0755);
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
            "public"
        ];

        foreach ($directories as $dir) {
            mkdir($dir, 0755, true);
        }

        file_put_contents("app/controllers/$appName/IndexController.php", $this->template("app/controllers/IndexController.php", [
            'AppName' => $appName
        ]));

        file_put_contents("app/views/index/index.phtml", "<h1>Hello World</h1>");

        file_put_contents("app/views/layouts/default.phtml", $this->template("app/views/layouts/default.phtml"));
        file_put_contents("public/index.php", $this->template("public/index.php"));
        file_put_contents("config/bootstrap.php", $this->template("config/bootstrap.php"));
        file_put_contents("config/routes.php", $this->template("config/routes.php"));
        file_put_contents("config/environments/production.php", $this->template("config/environments/production.php"));
        file_put_contents("config/environments/development.php", $this->template("config/environments/development.php"));

        file_put_contents("composer.json", $this->template("composer.json"));
        file_put_contents("README.txt", $this->template("README.txt"));

        file_put_contents("config/application.php", $this->template("config/application.php", [
            "AppName" => $appName
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
