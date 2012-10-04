<?php

namespace Spark\Core\Command;

use Silex\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use CG\Generator\DefaultVisitor;
use CG\Generator\DefaultNavigator;
use CG\Generator\PhpClass;
use CG\Generator\PhpMethod;

use Spark\Support\Strings;
use CHH\FileUtils\Path;

class GenerateController extends Command
{
    protected $application;

    function __construct(Application $app)
    {
        $this->application = $app;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('generate:controller')
            ->setDescription('Generates Controllers')
            ->addArgument('name', InputArgument::REQUIRED, 'Controller Name')
            ->addArgument('options', InputArgument::OPTIONAL, 'Generator options as key:value');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $options = $input->getArgument('options');

        $controllerPath = $this->application['spark.controller_directory'];
        $viewPath = $this->application['spark.view_path'][0];

        $module = $this->application['spark.default_module'];

        if (!is_dir("$viewPath/$name")) {
            mkdir("$viewPath/$name");
            file_put_contents("$viewPath/$name/index.phtml", "<h1>Hello World</h1>\n");
        }

        $className = Strings::camelize($name, true) . "Controller";
        $moduleName = Strings::camelize($module, true);

        $fileName = Path::join([$controllerPath, $module, "$className.php"]);

        if (is_file($fileName)) {
            $output->writeln("<error>Controller '$name' (" . realpath($fileName) . ") already exists.");
            return 1;
        }

        $applicationController = '\\' . Strings::camelize($this->application['spark.default_module'], true) . '\\' . "ApplicationController";

        $class = new PhpClass("$moduleName\\{$className}");
        $class->setParentClassName($applicationController);
        $class->setMethod(
            PhpMethod::create("indexAction")
        );

        $visitor = new DefaultVisitor;
        $navigator = new DefaultNavigator;

        $navigator->accept($visitor, $class);

        file_put_contents($fileName, "<?php\n\n" . $visitor->getContent());

        $output->writeln("Generated controller <info>$name</info> in <info>" . realpath($fileName) . "</info>");
    }
}
