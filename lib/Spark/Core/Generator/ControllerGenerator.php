<?php

namespace Spark\Core\Generator;

use CG\Generator\DefaultVisitor;
use CG\Generator\DefaultNavigator;
use CG\Generator\PhpClass;
use CG\Generator\PhpMethod;

use Silex\Application;
use Spark\Support\Strings;

use CHH\FileUtils\Path;

class ControllerGenerator extends AbstractGenerator
{
    function generate($name, $options = [])
    {
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
            $this->out->writeln("<error>Controller '$name' (" . realpath($fileName) . ") already exists.</error>");
            return false;
        }

        $applicationController = '\\' . Strings::camelize($this->application['spark.default_module'], true) . '\\' . "ApplicationController";

        $class = new PhpClass("$moduleName\\{$className}");
        $class->setParentClassName($applicationController);
        $class->setMethod(
            PhpMethod::create("indexAction")
        );

        $visitor = new DefaultVisitor;

        (new DefaultNavigator)->accept($visitor, $class);

        file_put_contents($fileName, "<?php\n\n" . $visitor->getContent());

        $this->out->writeln("Generated controller <info>$name</info> in " . realpath($fileName));
    }
}
