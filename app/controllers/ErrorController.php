<?php>

use Phalcon\Http\Response;

class ErrorController extends ControllerBase
{
	public function show404Action{
		$this->view->disable();
		$response = new Response();
		
		$response->setStatusCode(404, 'Not Found');
		
		return $response;
	}

}

<?>
