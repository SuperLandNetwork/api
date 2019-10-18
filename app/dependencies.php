<?php
/**
 * MIT License
 *
 * Copyright (c) 2019 Filli Group (Einzelunternehmen)
 * Copyright (c) 2019 Filli IT (Einzelunternehmen)
 * Copyright (c) 2019 Ursin Filli
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

// DIC configuration

$container = $app->getContainer();

// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------

// Database
$container['capsule'] = function ($c) {
    $capsule = new Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($c['settings']['db']);
    return $capsule;
};

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    if ($_SERVER['API_RUNTIME_MODE'] == 'Dev') {
        $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], Monolog\Logger::DEBUG));
    } else {
        $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], Monolog\Logger::INFO));
    }
    return $logger;
};

// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------

$container[App\Action\BaseAction::class] = function ($c) {
    return new App\Action\BaseAction($c->get('logger'));
};

// -----------------------------------------------------------------------------
// Error Pages
// -----------------------------------------------------------------------------

// File Not Found
$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $array = [
            'error' => 'Not Found',
            'errorMessage' => 'The server has not found anything matching the request URI',
        ];
        return $response->withStatus(404)->withJson($array);
    };
};

// Method Not Allowed
$container['notAllowedHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $array = [
            'error' => 'Not Allowed',
            'errorMessage' => 'Method not allowed',
        ];
        return $response->withStatus(405)->withJson($array);
    };
};

// Internal Server Error
$container['errorHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $array = [
            'error' => 'Internal Server Error',
            'errorMessage' => 'Internal Server Error',
        ];
        return $response->withStatus(500)->withJson($array);
    };
};