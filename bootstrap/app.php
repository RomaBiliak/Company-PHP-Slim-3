<?php
session_start();
require __DIR__ .'/../vendor/autoload.php';  



  
$app = new \Slim\App([
	'settings'=> [
		'displayErrorDetails'=>true,
		'pdo' => [
				'engine' => 'mysql',
				'host' => 'localhost',
				'database' => 'database',
				'username' => 'root',
				'password' => '123456',
				'charset' => 'utf8',
				'collation' => 'utf8_unicode_ci',
				'options' => [
						PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
						PDO::ATTR_EMULATE_PREPARES   => true,
					],
			],
	]
]);
$container = $app->getContainer();

$container['dbh'] = function($container) {

        $config = $container->get('settings')['pdo'];
        $dsn = "{$config['engine']}:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $username = $config['username'];
        $password = $config['password'];
        return new PDO($dsn, $username, $password, $config['options']);

};

$container['CompanyController'] = function($container){
	return new \App\Controllers\CompanyController($container);
};
$container['company'] = function($container){
	return new \App\Models\Company;
};


require __DIR__ .'/../app/routes.php';
