<?php

namespace Spark\Core\Command;

use Silex\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateController extends Command
{
    protected $application;

    function __construct(Application $app)
    {
        $this->application = $app;
    }

    protected function configure()
    {
        $this->setName('generate:controller')
            ->setDescription('Generates Controllers')
            ->addArgument('name', InputArgument::REQUIRED, 'Controller Name')
            ->addArgument('options', InputArgument::OPTIONAL, 'Generator options as key:value');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $name = $input->getArgument('name');
        $options = $input->getArgument('options');

        $controllerPath = $this->application['spark.controller_directory'];
        $module = $this->application['spark.default_module'];

        $className = Strings::camelize($name);
        $moduleName = Strings::camelize($module);

        $fileName = Path::join([$controllerPath, $module, "$className.php"]);

        $class = new PhpClass("$moduleName\\$className");
        $class->setMethod(
            PhpMethod::create("indexAction")
        );

        $visitor = new DefaultVisitor;
        $visitor->startVisitingClass($class);
        $visitor->endVisitingClass($class);

        $output->writeln($visitor->getContent());
    }
}
