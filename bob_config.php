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
    sh("phpunit");
});

fileTask("phpunit.xml", array("phpunit.dist.xml"), function($task) {
    copy($task->prerequisites[0], $task->name);
});

fileTask("composer.lock", array("composer.json", "deps"), function($task) {
    php("composer.phar update --dev");
});

$libFiles = fileList("*.php")->in("lib/");

fileTask("spark.phar", $libFiles, function($task) {
    sh("php box.phar build", null, ["fail_on_error" => true]);
    println("Built PHAR successfully to 'spark.phar'");
});

desc("Builds the PHAR");
task("build", ["composer.json", "spark.phar"]);

desc('Builds the PHAR and puts it onto the Github page');
task('gh-pages', ['build'], function() {
    $temp = 'spark_ghpages_clone_' . uniqid();
    $phar = realpath('spark.phar');

    cd(sys_get_temp_dir(), function() use ($phar, $temp) {
        sh(['git', 'clone', '--branch', 'gh-pages', 'git@github.com:CHH/spark', sys_get_temp_dir() . "/$temp"]);
        chdir($temp);

        copy($phar, "spark.phar");

        sh('git add spark.phar');
        sh('git commit -m "Update spark.phar"');

        sh('git push git@github.com:CHH/spark gh-pages');
    });
});

