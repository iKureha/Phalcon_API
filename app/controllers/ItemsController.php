<?php

use Phalcon\Http\Request;
use Phalcon\Http\Response;

class ItemsController extends ControllerBase
{

	public function indexAction($id)
	{
		$this->view->disable();
		$request = new Request();				
		$response = new Response();
		
	
		// Get data by id
		if (!empty($id) && $request->isGet()){
			# echo $id;
			$item = Items::findFirst($id);	
			
			if ($item == true){
				$data[] = [
					'id' => $item->id,
					'title' => $item->title,
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
			}
			return $response;
		}
		

		// Get all the data from db
		if (empty($id) && $request->isGet())
		{	
			$items = Items::find();
			
			foreach ($items as $item){
				$data[] =[
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
			
			return $response;
		}
		
	}



}




?>
