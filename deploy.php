<?php

// https://github.com/deployphp/deployer/blob/master/docs/api.md
// https://github.com/deployphp/deployer/tree/master/docs/recipe/deploy

namespace Deployer;

require_once __DIR__.'/vendor/autoload.php';
require 'recipe/composer.php';

set('application', 'secrets');
set('repository', 'https://github.com/Luca-Castelnuovo/secrets.git');
set('use_relative_symlink', false);

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

host('production')
    ->setHostname('86.87.160.103')
    ->setPort(22)
    ->setRemoteUser('webserver')
    ->setSshMultiplexing(false)
    ->setDeployPath('~/{{application}}');
    // ->setIdentityFile(__DIR__ . '/.env');

task('build', function () {
    cd('{{release_path}}');
    run('touch test');
});

after('deploy:failed', 'deploy:unlock');
