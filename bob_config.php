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

fileTask("_build/spark.phar", $libFiles, function($task) {
    sh("php box.phar build -v", null, ["fail_on_error" => true]);
    println("Built PHAR successfully to 'spark.phar'");
});

directoryTask('_build');

desc("Builds the PHAR");
task("dist", ['_build', "composer.json", "_build/spark.phar"]);

desc('Builds the PHAR and puts it onto the Github page');
task('gh-pages', ['docs', 'dist'], function() {
    $temp = 'spark_ghpages_clone_' . uniqid();
    $phar = realpath('_build/spark.phar');
    $api = realpath('_build/api');

    cd(sys_get_temp_dir(), function() use ($phar, $temp, $api) {
        sh(['git', 'clone', '--branch', 'gh-pages', 'git@github.com:CHH/spark', sys_get_temp_dir() . "/$temp"]);
        chdir($temp);

        info("Updating API Documentation ...");

        sh('rm -rf api/');
        sh("cp -R $api ./");
        sh('git add --all api/');
        sh('git commit -m "Update API documentatino"');

        info("Updating spark.phar ...");

        copy($phar, "spark.phar");
        sh('git add spark.phar');
        sh('git commit -m "Update spark.phar"');

        sh('git push git@github.com:CHH/spark gh-pages');
    });
});

desc('Builds the documentation');
task('docs', ['_build'], function() {
    php(['vendor/bin/sami.php', 'update', 'sami_config.php', '-v']);
});

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

