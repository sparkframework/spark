<?php

namespace Bob\BuildConfig;

task("default", array("build"));

desc('Sets up development dependencies');
task('dev', array('deps', 'composer.json'));

task('deps', function() {
    if (!is_file('composer.phar')) {
        $src = fopen('http://getcomposer.org/composer.phar', 'rb');
        $dest = fopen('composer.phar', 'w+');

        stream_copy_to_stream($src, $dest);

        fclose($src);
        fclose($dest);

        chmod('composer.phar', 0755);
    }

    if (!is_file('box.phar')) {
        sh('curl -s http://box-project.org/installer.php | php');
    }
});

task('clean', function() {
    unlink("spark.phar");
});

desc("Runs all tests.");
task("test", array("deps", "phpunit.xml", "composer.json"), function() {
    sh("vendor/bin/phpunit");
});

fileTask("phpunit.xml", array("phpunit.dist.xml"), function($task) {
    copy($task->prerequisites[0], $task->name);
});

fileTask("composer.lock", array("composer.json", "deps"), function($task) {
    php("composer.phar update --dev");
});

$libFiles = fileList("*.php")->in("lib/");

fileTask("spark.phar", $libFiles, function($task) {
    sh("php box.phar build -v", null, ["fail_on_error" => true]);
    println("Built PHAR successfully to 'spark.phar'");
});

desc("Builds the PHAR");
task("dist", ["composer.json", "spark.phar"]);

desc(
    'Releases a version. Usage: bob release version=<version>'
);
task('release', function() {
    $version = $_ENV['version'];

    sh("git checkout -b release/$version");

    $spark = file_get_contents('lib/Spark/Spark.php');

    file_put_contents(
        'lib/Spark/Spark.php',
        preg_replace(
            '/const VERSION = ".*";/i', sprintf('const VERSION = "%s";', $version),
            file_get_contents('lib/Spark/Spark.php')
        )
    );

    sh(sprintf('git commit lib/Spark/Spark.php -m "Update version to %s"', $version));

    task('gh-pages')->invoke();
});

