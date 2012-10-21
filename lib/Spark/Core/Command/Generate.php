<?php

namespace Spark\Core\Command;

use Silex\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Spark\Core\Generator\GeneratorInterface;

class Generate extends Command
{
    protected $application;
    protected $generators = [];

    function __construct(Application $app)
    {
        $this->application = $app;
        parent::__construct();
    }

    function register($type, GeneratorInterface $generator)
    {
        $this->generators[$type] = $generator;
        return $this;
    }

    protected function configure()
    {
        $this->setName('generate')
            ->setAliases(array('g'))
            ->setDescription('Generate Application Assets')
            ->addOption('list', '', InputOption::VALUE_NONE)
            ->addArgument('generator', InputArgument::OPTIONAL, 'Generator name')
            ->addArgument('name', InputArgument::OPTIONAL, 'Artifact Name')
            ->addArgument('options', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Options as colon value pairs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('list')) {
            echo join("\n", array_keys($this->generators)) . "\n";
            return 0;
        }

        $name = $input->getArgument('name');
        $generator = $input->getArgument('generator');
        $options = [];

        foreach ((array) $input->getArgument('options') as $option) {
            list($key, $value) = explode(':', $option);
            $options[$key] = $value;
        }

        if (!isset($this->generators[$generator])) {
            $output->writeln(sprintf('Generator <error>%s</error>', $generator));
            return 1;
        }

        $generator = $this->generators[$generator];
        $generator->setOutput($output);
        $generator->setApplication($this->application);

        return $generator->generate($name, $options) ? 0 : 1;
    }
}
