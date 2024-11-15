<?php
/**
 * @version 0.5
 *
 * Base deployment script for shopware6.
 * Do not modify this file without a good reason.
 * Everything can be override in the deployer.php file.
 */
namespace Deployer;

require 'vendor/deployer/deployer/recipe/common.php';

/**
 * We replace the default update_code task because we don't want the
 * whole git repository on stage/production just the files.
 */
task('deploy:update_code', function () {
    run('git archive --remote {{repository}} --format tar {{branch}} | (cd {{release_path}} && tar xf -)');
});

/**
 * List of paths which need to be deleted in release after updating code.
 */
set('clear_paths', [
    '.editorconfig',
    'phpunit.xml.dist',
    '.lando.yml',
    '.lando_alias',
    'README.md'
]);

/**
 * We replace the default release_name with a date based.
 */
set('release_name', function () {
    return date('Ymd-His');
});

/**
 * As long as we want to be able to use the env=dev on the remote server
 * We need the "symfony/web-profiler-bundle" and can't use the '--no-dev'
 * Parameter
 */
set('composer_options', '--verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader --no-suggest');

/**
 * Some tasks e.g. update/install composer vendors may take some time and
 * the default 300 is way to low.
 */
set('default_timeout', 1200);

/**
 * All files that should be in shared. These are independent of the current release.
 * If a file exists in the repository but not in the shared folder on the server
 * the file is copied before the symlink is created.
 *
 * Later updates to these files in the repository are ignored on deployment.
 * You have to make the changes in the shared-folder yourself.
 */
set('shared_files', [
    '.env',
    //'install.lock',
    'public/.htaccess'
]);

/**
 * Special case install.lock
 * The "shared_files" logic create the file if not exists before creating the symlink.
 * But for the initial deployment we need a install.lock symlink but the referenced install.lock  must not exist
 * Otherwise we get problems running composer install which also execute some other commands if install.lock exists.
 */
task('sw:create:installlock:symlink', static function () {
    $sharedPath = "{{deploy_path}}/shared";
    $file = 'install.lock';
    run("{{bin/symlink}} $sharedPath/$file {{release_path}}/$file");
});

/**
 * All folders that should be in shared. These are independent of the current release.
 *
 * If a folder exists in the repository but not in the shared folder on the server
 * the folder is copied before the symlink is created.
 *
 * Later updates to these folders in the repository are ignored on deployment.
 * You have to make the changes in the shared-folder yourself.
 */
set('shared_dirs', [
//    'custom/plugins',
    'config/jwt',
    'files',
    'var/log',
    'public/media',
    'public/thumbnail',
    'public/sitemap',
    'config/packages/instance-specific'
]);

/**
 * List of dirs which must be writable for web server.
 */
set('writable_dirs', [
    'custom/plugins',
    'config/jwt',
    'files',
    'var',
    'public/media',
    'public/thumbnail',
    'public/sitemap',
]);

/**
 * @see https://github.com/deployphp/deployer/blob/master/recipe/shopware6.php
 *
 * This tasks installed and activates all plugins available in the project.
 *
 * You can add Plugins to the pluginBlacklist in your project specific deployer.php
 * to ignore/skip these plugins.
 */
set('pluginBlacklist', []);

/**
 * This is necessary because deploy:writable sets the permission recursively for all
 * folders and files. This problem does not seem to be present in all `writable_modes`.
 * With "acl" for example not with "chmod" it does.
 *
 * As long as it has no negative effects, we still execute these tasks independent
 * of the selected mode.
 *
 * For SW6 'config/jwt/private.pem' and 'config/jwt/public.pem' are not allowed to be
 * 755.
 */
set('file permissions', [
    ['config/jwt/private.pem', 600],
    ['config/jwt/public.pem', 600]
]);

desc('Set file permissions');
task('deploy:file_permissions', function () {
    $files = get('file permissions');
    if (is_array($files) && count($files) > 0) {
        cd('{{release_path}}');
        foreach ($files as $file) {
            /**
             * This would execute :, i.e. the null command, if chmod fails.
             * Since the null command does nothing but always succeeds,
             * you would see an exit code of 0.
             */
            run("chmod $file[1] $file[0] || :");
        }
    }
});

/**
 * @see https://docs.shopware.com/en/shopware-platform-dev-en/references-internals/plugins/plugin-migrations
 * @see https://docs.shopware.com/en/shopware-platform-dev-en/developer-guide/migrations
 * @see https://docs.shopware.com/en/shopware-platform-dev-en/how-to/plugin-migrations
 * @see vendor/shopware/core/Framework/Migration/Command/MigrationCommand.php
 *
 * This only run migrations from 'core' not plugin related migrations.
 */
task('sw:database:migrate', static function () {
    run('cd {{release_path}} && bin/console database:migrate --all');
});

/**
 * Complete deployment task
 */
task('deploy', [
    'deploy:prepare',
    'sw:create:installlock:symlink',
    'deploy:vendors',
    'deploy:writable',
    'deploy:file_permissions',
    'deploy:clear_paths',
    'deploy:publish'
])->desc('Deploy project');

after('deploy:failed', 'deploy:unlock');
