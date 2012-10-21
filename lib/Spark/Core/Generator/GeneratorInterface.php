<?php

namespace Spark\Core\Generator;

use Spark\Core\ApplicationAware;
use Symfony\Component\Console\Output\OutputInterface;

interface GeneratorInterface extends ApplicationAware
{
    function generate($name, $options = []);

    function setOutput(OutputInterface $output);
}

