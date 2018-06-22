<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Customer;
use App\Controllers\CustomerController;
// Routes

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Access-Control-Allow-Headers, X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/', function (Request $request, Response $response) {
    return $this->renderer->render($response, 'index.phtml');
});

$app->get('/setupbasedata', function(Request $request, Response $response) {
    if(!Customer::where('cid', '1234')->first()) {
        $customer = Customer::create([
            'name' => 'Test Testerssson',
            'cid' => '1234'
        ]);

        $response->getBody()->write("Test customer set up <br>");
    }

    $response->getBody()->write("All data has been set up");
    return $response;
});

$app->get('/registercard/{cid}', function(Request $request, Response $response, $args) {
    $customer = $this->CustomerController->checkSession($request->getParam('token'), $args['cid']);
    if(!$customer) {
        $response->getBody()->write("Customer not found!");
        return $response;
    }

    return $this->renderer->render($response, 'terms.phtml', [
        'customerid' => $args['cid'],
        'token' => $request->getParam('token')
    ]);
});

$app->get('/doregistercard/{cid}', function (Request $request, Response $response, $args) {
    $customer = $this->CustomerController->checkSession($request->getParam('token'), $args['cid']);
    if(!$customer) {
        $response->getBody()->write("Customer not found!");
        return $response;
    }
    //Register a card for later processing
    $this->CustomerController->registerCard($customer->id);

    $service_url = 'http://zeipt.io/zeipt/RegisterCard/';
    $username = 'alex';
    $password = 'zeipt.com';
    $curl = curl_init($service_url);
    $curl_post_data = array(
        'GCID' => $customer->cid
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
});

/* Deprecated, use new POST function */
$app->get('/registercustomer/', function (Request $request, Response $response, $args) {
    $name = $request->getParam('name');
    $pass = $request->getParam('pass');
    $res = $this->CustomerController->createCustomer($name, $pass);
    if($res) {
        $loginRes = $this->CustomerController->login($name, $pass);
        if($loginRes) {
            return $response->withJson([
                'success' => 1,
                'msg' => "Customer $name created",
                'session_token' => $loginRes['session_token'],
                'cid' => $res->cid
            ]);
        }
    }

    return $response->withJson([
        'success' => 0,
        'msg' => "Failed to create customer!"
    ]);
});

$app->post('/registercustomer/', function (Request $request, Response $response, $args) {
    $name = $request->getParam('name');
    $pass = $request->getParam('pass');
    $res = $this->CustomerController->createCustomer($name, $pass);
    if($res) {
        $loginRes = $this->CustomerController->login($name, $pass);
        if($loginRes) {
            return $response->withJson([
                'success' => 1,
                'msg' => "Customer $name created",
                'session_token' => $loginRes['session_token'],
                'cid' => $res->cid
            ]);
        }
    }

    return $response->withJson([
        'success' => 0,
        'msg' => "Failed to create customer!"
    ]);
});

$app->get('/SuccessPage', function (Request $request, Response $response) {
    $gcid = $request->getParam('GCID');
    $token = $request->getParam('Token');
    $type = $request->getParam('CardType');
    $expire = $request->getParam('CardExpDate');
    $maskedNum = $request->getParam('CardMaskedPan');
    if(strlen($gcid) > 0) {
        $customer = Customer::where('cid', $gcid)->first();
        if($customer) {
            $this->CustomerController->saveCard($customer->id, $maskedNum, $expire, $type);
            return $this->renderer->render($response, 'success.phtml');
        }
    }
    return $this->renderer->render($response, 'fail.phtml');
});

$app->get('/FailPage', function (Request $request, Response $response) {
    $gcid = $request->getParam('GCID');
    if(strlen($gcid) > 0) {
        $customer = Customer::where('cid', $gcid)->first();
        $this->CustomerController->clearRegisteredCards($customer->id);
    }
    return $this->renderer->render($response, 'fail.phtml');
});

$app->get('/receipts/{cid}', function(Request $request, Response $response, $args) {
    $customer = $this->CustomerController->checkSession($request->getParam('token'), $args['cid']);
    if(!$customer) {
        return $response->withJson([
            'success' => 0,
            'msg' => 'Invalid credentials'
        ]);
    }

    $service_url = 'http://zeipt.io/zeipt/ReceiptZeipt/';
    $username = 'alex';
    $password = 'zeipt.com';
    $curl = curl_init($service_url);
    $curl_post_data = array(
        'GCID' => $customer->cid
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
});

$app->get('/cards/{cid}', function(Request $request, Response $response, $args) {
    $customer = $this->CustomerController->checkSession($request->getParam('token'), $args['cid']);
    if($customer) {
        return $response->withJson([
            'success' => 1,
            'cards' => $customer->cards
        ]);
    } else {
        return $response->withJson([
            'success' => 0,
            'msg' => 'Invalid credentials'
        ]);
    }
});

$app->post('/login/', function(Request $request, Response $response, $args) {
    $name = $request->getParam('name');
    $pass = $request->getParam('password');
    if(!$name || !$pass || strlen($name) == 0 || strlen($pass) == 0) {
        return $response->withJson([
            'success' => 0,
            'msg' => 'Missing parameters'
        ]);
    }
    $res = $this->CustomerController->login($name, $pass);
    if(!$res) {
        return $response->withJson([
            'success' => 0,
            'msg' => 'Wrong username or password'
        ]);
    } else {
        $this->CustomerController->clearRegisteredCards($res['customer']->id);
        return $response->withJson([
            'success' => 1,
            'GCID' => $res['customer']->cid,
            'session_token' => $res['session_token']
        ]);
    }
});

$app->post('/refreshLogin/{cid}', function(Request $request, Response $response, $args) {
    $customer = $this->CustomerController->checkSession($request->getParam('token'), $args['cid']);
    if($customer) {
        $this->CustomerController->clearRegisteredCards($customer->id);
        return $response->withJson([
            'success' => 1,
            'session_token' => $this->CustomerController->makeSession($customer)
        ]);
    } else {
        return $response->withJson([
            'success' => 0,
            'msg' => 'Session could not be refreshed. Please log in again'
        ]);
    }
});

$app->post('/deletecard/{cid}/{cardid}', function(Request $request, Response $response, $args) {
    $customer = $this->CustomerController->checkSession($request->getParam('token'), $args['cid']);
    if(!$customer) {
        return $response->withJson([
            'success' => 0,
            'msg' => 'Invalid credentials'
        ]);
    }

    if($this->CustomerController->removeCard($customer->id, $args['cardid'])) {
        return $response->withJson([
            'success' => 1,
            'msg' => 'Card removed'
        ]);
    } else {
        return $response->withJson([
            'success' => 0,
            'msg' => 'No card to remove'
        ]);
    }
});

$app->post('/tempregcard/{cid}', function(Request $request, Response $response, $args) {
    //Create a temporary card for this user
    $customer = $this->CustomerController->checkSession($request->getParam('token'), $args['cid']);

    if(!$customer) {
        return $response->withJson([
            'success' => 0,
            'msg' => 'Invalid credentials'
        ]);
    }

    $lastfour = $request->getParam('lastfour');
    $type = $request->getParam('type');
    $expires = $request->getParam('expires');
    if($lastfour && $expires && $type) {
        $res = $this->CustomerController->createCard($customer->id, $lastfour, $expires, $type);
        if($res) {
            return $response->withJson([
                'success' => 1,
                'card' => $res
            ]);
        }
    }

    return $response->withJson([
        'success' => 0,
        'msg' => 'Failed to create card'
    ]);
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});