<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Customer;
use App\Controllers\CustomerController;
// Routes

$app->get('/', function (Request $request, Response $response) {
    // Sample log message
    //$this->logger->info($this->db->lastErrorMsg());

    // Render index view
    return $this->renderer->render($response, 'index.phtml');
});

$app->get('/registercard/{cid}', function (Request $request, Response $response, $args) {
    //ob_start();
    $customer = Customer::where('cid', $args['cid'])->first();
    $service_url = 'http://zeipt.io/verifone/RegisterCard/';
    $username = 'alex';
    $password = 'zeipt.com';
    $curl = curl_init($service_url);
    $curl_post_data = array(
        'GCI' => $customer->cid;
    );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
    curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $curl_response = curl_exec($curl);

    curl_close($curl);
    $response->getBody()->write($curl_response);
    return $response;
    //echo($curl_response);
});

$app->get('/registercustomer/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $res = $this->CustomerController->createCustomer($name);
    if($res) {
        return $response->withJson([
            'success' => 'true',
            'msg' => "Customer $name created"
        ]);
    } else {
        return $response->withJson([
            'success' => 'false',
            'msg' => "Customer $name already exists"
        ]);
    }
});

$app->get('/test', function(Request $request, Response $response) {
    return;
});