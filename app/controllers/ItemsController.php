<?php

use Phalcon\Http\Request;
use Phalcon\Http\Response;

class ItemsController extends ControllerBase
{

	public function indexAction($id)
	{
		// because this is an API, disable view 
		$this->view->disable();

		$request = new Request();				
		$response = new Response();
	
		switch ($request->getMethod()) {
			
			// For test
			case HEAD:
				
			// Get data by id
			case 'GET':
				echo "GET";
				if (!empty($id)){
					$item = Items::findFirst($id);	
							
					if ($item){
						$data[] = [
							'id' => $item->id,
							'title' => $item->title,
							'price' => $item->price,
							'description' => $item->description,
							'has_image' => $item->has_image,
							'update_time' => $item->update_time,
						];
			
						$response->setJsonContent(
							[
								'status' => 'FOUND',
								'data' => $data,
							], JSON_UNESCAPED_UNICODE
						);
					}
					else {
						$response->setJsonContent(['status' => 'Not Found']);
						$response->setStatusCode(404, 'Not Found');
					}
				}

				// Get all the data from db
				else {
					$items = Items::find();
			
					foreach ($items as $item){
						$data[] = [
							'id' => $item->id,
							'title' => $item->title,
						];
					}
			
					$response->setStatusCode(200, 'OK');
					$response->setJsonContent(
						[
							'status' => 'OK',
							'data' => $data,
						], JSON_UNESCAPED_UNICODE
					);
			
				}

				break;

		
			// update data/image by id 
			case 'POST':
				echo "POST";
				$item = new Items;

				// check if there is an image
				if	($request->hasFiles() && !empty($id)) {
					foreach ($request->getUploadedFiles() as $file) {					
						$img_path = "/var/www/html/my-rest-api/public/img/" . $file->getName();
						$file->moveTo($img_path);
					}
					
					$item = Items::findFirst($id);			
					if ($item){
						$item->image = $img_path;
						$item->has_image = '1';
						$item->update();		
	
						$response->setJsonContent(
							[
								'status' => 'Updated',
							], JSON_UNESCAPED_UNICODE
						);
					}
					else {
						$response->setJsonContent(['status' => 'No this item']);
					}
					break;
				}

				// if there is no image, update item			
				$data = json_decode($request->getRawBody(), true);
				// アップロードされたデータはjsonではない場合、中止
				if (empty($data)) {return "Bad Json!";} 

				$item = Items::findFirst($id);	
				if ($item->update($data)) {
					$response->setStatusCode(201, 'Updated');
					$response->setJsonContent(
						[
							'status' => 'Updated',
							'data' => $data, 
						], JSON_UNESCAPED_UNICODE
					);
				} else {
					$response->setStatusCode(409, 'Conflict');
					$response->setJsonContent(
						[
							'status' => 'Error',
						], JSON_UNESCAPED_UNICODE
					);		
				}
				break;
				


			// create a new item
			case 'PUT':
				echo "put";
				$item = new Items;
				$data = json_decode($request->getRawBody(), true);
				
				// アップロードされたデータはjsonではない場合、中止
				if (empty($data)) {return "Bad Json!";}
				
				if ($item->save($data)) {
					$response->setStatusCode(201, 'Created');
					$response->setJsonContent(
						[
							'status' => 'Created',
							'data' => $data, 
						], JSON_UNESCAPED_UNICODE
					);
				} else {
					$response->setStatusCode(409, 'Conflict');
					$response->setJsonContent(
						[
							'status' => 'Error',
						], JSON_UNESCAPED_UNICODE
					);		
				}
				
				break;
				

			// delete data by id
			case 'DELETE':
				echo "delete";
				$item = Items::findFirst($id);	
				if ($item->delete()) {
					$response->setStatusCode(204, 'Deleted');
					$response->setJsonContent(
						[
							'status' => 'Deleted',
						], JSON_UNESCAPED_UNICODE
					);
				} else {
					$response->setStatusCode(409, 'Conflict');
					$response->setJsonContent(
						[
							'status' => 'Error',
						], JSON_UNESCAPED_UNICODE
					);		
				}
				break;


			default:
				$response->setJsonContent(['status' => 'Not Found']);
				$response->setStatusCode(404, 'Not Found');
				break;
		}
		return $response;
	}

}



?>
