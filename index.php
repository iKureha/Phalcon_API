<?php


///////////////////////////////
// This file is not in using //
///////////////////////////////


use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Http\Response;

// Use Loader() to autoload our model
$loader = new Loader();

$loader->registerNamespaces(
    [
        'Test' => __DIR__ . '/models/',
    ]
);

$loader->register();

$di = new FactoryDefault();

// Set up the database service
$di->set(
    'db',
    function () {
        return new PdoMysql(
            [
                'host'     => 'localhost',
                'username' => 'wang',
                'password' => 'wangyu123',
                'dbname'   => 'php_shopping',
            ]
        );
    }
);

// Create and bind the DI to the application
$app = new Micro($di);

/////////////////////////////
// Define the routes below //
/////////////////////////////

// Return all the data
$app->get(
    '/api/items',
    function () use ($app) {
        $phql = 'SELECT * FROM Test\Items ORDER BY id';

        $items = $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($items as $item) {
            $data[] = [
                'id'   => $item->id,
                'title' => $item->title,
				'price' => $item->price,
				'description' => $item->description,
            ];
        }

		//use JSON_UNESCAPED_UNICODE to echo Japanese correctly
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
        //echo json_encode($data);
    }
);

// Search data by name
$app->get(
    '/api/items/search/{name}',
    function ($name) use ($app) {
        $phql = 'SELECT * FROM Test\Items WHERE title LIKE :name: ORDER BY title';

        $items = $app->modelsManager->executeQuery(
            $phql,
            [
                'name' => '%' . $name . '%'
            ]
        );

        $data = [];
		
		$response = new Response();

        if ($items === false) {
            $response->setJsonContent(
                [
                    'status' => 'NOT-FOUND'
                ]
            );
        } else {
			foreach ($items as $item) {
            	$data[] = [
            	'id'   => $item->id,
             	'title' => $item->title,
             	'price' => $item->price,
             	'description' => $item->description,
             	];
         	}
            $response->setJsonContent(
                [
                    'status' => 'FOUND',
                    'data'   => $data,
                ], JSON_UNESCAPED_UNICODE
            );
        }

        return $response;
    }
);

// Insert data
$app->post(
    '/api/items',
    function () use ($app) {
        $item = $app->request->getJsonRawBody();

		// Use Phql to execute SQL 
		$phql = 'INSERT INTO Test\Items (title, price, description) VALUES (:title:, :price:, :description:)'; 
		$status = $app->modelsManager->executeQuery(
			$phql,
			[   
 				'title' => $item->title,
 				'price' => $item->price,
 				'description' => $item->description,
			]
 		);

        // Create a response
        $response = new Response();

        // Check if the insertion was successful
        if ($status->success() === true) {
            // Change the HTTP status
            $response->setStatusCode(201, 'Created');

            $item->id = $status->getModel()->id;

            $response->setJsonContent(
                [
                    'status' => 'OK',
                    'data'   => $item,
                ], JSON_UNESCAPED_UNICODE
            );
        } else {
            // Change the HTTP status
            $response->setStatusCode(409, 'Conflict');

            // Send errors to the client
            $errors = [];

            foreach ($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }

            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => $errors,
                ]
            );
        }

        return $response;
    }
);

// Insert a image by using shopping id
$app->post(
    '/api/items/image/{id:[0-9]+}',
    	function ($id) use ($app) {
        	$item = $app->request->getJsonRawBody();
			
			// Check if there is a img file
			if ($app->request->hasFiles()) {
				foreach ($app->request->getUploadedFiles() as $file) {
		 			echo "Image found! " . $file->getName() . PHP_EOL;
					try {
							$img_path = "/var/www/html/my-rest-api/public/img/" . $file->getName();
							$file->moveTo($img_path);
							echo "Upload Successful !!!";

        					$phql = 'UPDATE Test\Items SET image = :img_path: WHERE id = :id:';
        					$status = $app->modelsManager->executeQuery(
				            	$phql,
            						[
                						'id'   => $id,
                						'img_path' => $img_path,
            						]
        					);

        					// Create a response
        					$response = new Response();

        					// Check if the insertion was successful
        					if ($status->success() === true) {
            					$response->setJsonContent(
                					[
                    					'status' => 'OK'
                					]
            					);
        					} else {
            				// Change the HTTP status
            					$response->setStatusCode(409, 'Conflict');
            					$errors = [];

            					foreach ($status->getMessages() as $message) {
                					$errors[] = $message->getMessage();
            					}

            					$response->setJsonContent(
                					[
                  						'status'   => 'ERROR',
                   	 					'messages' => $errors,
                					]
            					);
        					}
        					return $response;
						}

					catch (Exception $e) {
						echo "Message: " . $e->getMessage();
					}
				}	
			} else {
				echo "no images found!";
			}

    }
);


// Update data by id
$app->put(
    '/api/items/{id:[0-9]+}',
    function ($id) use ($app) {
        $item = $app->request->getJsonRawBody();

        $phql = 'UPDATE Test\Items SET title = :title:, price = :price:, description = :description: WHERE id = :id:';

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                'id'   => $id,
                'title' => $item->title,
                'price' => $item->price,
                'description' => $item->description,
            ]
        );

        // Create a response
        $response = new Response();

        // Check if the insertion was successful
        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    'status' => 'OK'
                ]
            );
        } else {
            // Change the HTTP status
            $response->setStatusCode(409, 'Conflict');

            $errors = [];

            foreach ($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }

            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => $errors,
                ]
            );
        }

        return $response;
    }
);


$app->delete(
    '/api/items/{id:[0-9]+}',
    function ($id) use ($app) {
        $phql = 'DELETE FROM Test\Items WHERE id = :id:';

        $status = $app->modelsManager->executeQuery(
            $phql,
            [
                'id' => $id,
            ]
        );

        // Create a response
        $response = new Response();

        if ($status->success() === true) {
            $response->setJsonContent(
                [
                    'status' => 'OK'
                ]
            );
        } else {
            // Change the HTTP status
            $response->setStatusCode(409, 'Conflict');

            $errors = [];

            foreach ($status->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }

            $response->setJsonContent(
                [
                    'status'   => 'ERROR',
                    'messages' => $errors,
                ]
            );
        }

        return $response;
    }
);


$app->handle();


?>
