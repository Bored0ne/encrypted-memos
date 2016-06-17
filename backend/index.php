<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'config.php';

$app = new \Slim\App;
$app->post('/memoapi/login', function (Request $request, Response $response) {
  // $response->getBody()->write(var_export($request->getHeader('badge')[0], true));
  // $response->setStatus(400);
  $badge = $request->getHeader('badge')[0];
  $pin = $request->getHeader('pin')[0];
  if (!empty($badge) && !empty($pin))
  {
    $md5 = md5($badge . $pin);
    // $response->getBody()->write($md5);
    $query = "
    SELECT *
    FROM accounts
    WHERE badge = '{$badge}'
    AND pin = '{$pin}'";
    $connect = mysqli_connect(Config::dbHost, Config::dbUser, Config::dbPass);
    mysqli_select_db($connect, Config::dbName);
    $result = mysqli_query($connect, $query) or die(mysqli_error($connect));
    $assoc = mysqli_fetch_assoc($result);
    if (!empty($assoc))
    {
      $response->getBody()->write($assoc['auth_key']);
    }
    else
    {
      $query = "INSERT INTO accounts (badge, pin, auth_key)
      VALUES ('{$badge}', '{$pin}', MD5('{$badge}' + '{$pin}'))";
      $result = mysqli_query($connect, $query) or die(mysqli_error($connect));
      if ($result)
      {
        $query = "
        SELECT *
        FROM accounts
        WHERE badge = '{$badge}'
        AND pin = '{$pin}'";
        $result = mysqli_query($connect, $query) or die(mysqli_error($connect));
        $assoc = mysqli_fetch_assoc($result);
        $response->getBody()->write($assoc['auth_key']);
      }
    }
  }

  return $response;
});
$app->run();
