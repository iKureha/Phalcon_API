<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Request;


class ItemsController extends ControllerBase
{

	public function indexAction()
	{
		//
	}

	public function getAllAction()
	{
		$this->view->disable();
		$request = new Request();		

		if ($request->isGet())
		{
			$items = Items::find();
			
			foreach ($items as $item){
				$data[] =[
					'id' => $item->id,
					'title' => $item->title,
					'price' => $item->price,
					'description' => $item->description,
				];
			}
			echo json_encode($data, JSON_UNESCAPED_UNICODE);
		
		}
		else
		{
			echo 'Please use GET method!';
		}
		
	}



}




?>
