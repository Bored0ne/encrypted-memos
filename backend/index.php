<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'config.php';

$app = new \Slim\App;
// $app->response->headers->set('Access-Control-Allow-Origin', '*');
unset($app->getContainer()['errorHandler']);

function MakeMemoTable($connect)
{
  $query = <<<SQL
    CREATE TABLE `memos`.`memos` (
      `id` INT NOT NULL AUTO_INCREMENT COMMENT '',
      `memo` TEXT NOT NULL COMMENT '',
      `account_id` INT NOT NULL COMMENT '',
      `timestamp` DATETIME NOT NULL COMMENT '',
      PRIMARY KEY (`id`)  COMMENT '',
      UNIQUE INDEX `id_UNIQUE` (`id` ASC)  COMMENT '');

SQL;
  mysqli_query($connect, $query);
}

function MakeAccountTable($connect)
{
  $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `memos`.`accounts` (
      `id` INT NOT NULL AUTO_INCREMENT COMMENT '',
      `badge` VARCHAR(45) NOT NULL COMMENT '',
      `auth_key` VARCHAR(45) NOT NULL COMMENT '',
      PRIMARY KEY (`id`)  COMMENT '',
      UNIQUE INDEX `id_UNIQUE` (`id` ASC)  COMMENT '',
      UNIQUE INDEX `badge_UNIQUE` (`badge` ASC)  COMMENT '');
SQL;
  mysqli_query($connect, $query) or die(mysqli_error($connect));
}

$app->delete('/memoapi/memos/{id}', function ($request, $response, $args)
{
  $connect = mysqli_connect(Config::dbHost, Config::dbUser, Config::dbPass);
  mysqli_select_db($connect, Config::dbName);
  // $route = $request->getRoute();
  $id = mysqli_real_escape_string($connect, $args['id']);
  $key = mysqli_real_escape_string($connect, $request->getHeader('key'));

  $query = <<<SQL
    SELECT id
    FROM accounts
    WHERE auth_key = '{$key}'
SQL;
  $result = mysqli_query($connect, $query) OR $response->withStatus('key not associated with account' . PHP_EOL);
  $assoc = mysqli_fetch_assoc($result);
  $accountId = $assoc['id'];

  $query = <<<SQL
    DELETE FROM memos
    WHERE id = {$id}
      AND account_id = {$accountId}
SQL;
  mysqli_query($connect, $query);
});

$app->map(['PUT', 'POST'], '/memoapi/memos[/{id}]', function($request, $response, $args){
  $status = 200;
  $connect = mysqli_connect(Config::dbHost, Config::dbUser, Config::dbPass);
  mysqli_select_db($connect, Config::dbName);
  $key = mysqli_real_escape_string($connect, $request->getHeader('key')[0]);
  $memo = mysqli_real_escape_string($connect, $request->getBody());
  @$id = mysqli_real_escape_string($connect, $args['id']);

  $query = <<<SQL
    SELECT id
    FROM accounts
    WHERE auth_key = '{$key}'
SQL;
  $result = mysqli_query($connect, $query) OR $response->withStatus('key not associated with account' . PHP_EOL);
  $assoc = mysqli_fetch_assoc($result);
  $accountId = $assoc['id'];
  if (!empty($memo))
  {
    if (!empty($id))
    {
      $query = <<<SQL
        UPDATE memos
        SET memo = '{$memo}', timestamp = CURRENT_TIMESTAMP()
        WHERE id = '{$id}'
          AND account_id = '{$accountId}'
SQL;

$response->getBody()->write(var_export($query, true));
    }
    else
    {
      $query = <<<SQL
        INSERT INTO memos (memo, account_id, timestamp)
        VALUES ('{$memo}', '{$accountId}', CURRENT_TIMESTAMP())
SQL;
    }
    mysqli_query($connect, $query) OR DIE(mysqli_error($connect));
  }

  $response->header('Access-Control-Allow-Origin', '*');
  return $response->withStatus($status);
});

$app->get('/memoapi/memos[/{id}]', function($request, $response, $args){
  $status = 200;

  $connect = mysqli_connect(Config::dbHost, Config::dbUser, Config::dbPass);
  mysqli_select_db($connect, Config::dbName);

  $key = mysqli_real_escape_string($connect, $request->getHeader('key')[0]);
  @$quantity = mysqli_real_escape_string($connect, $request->getHeader('quantity')[0]);
  @$id = mysqli_real_escape_string($connect, $args['id']);

  $query = <<<SQL
    SELECT m.id, m.memo
    FROM memos m
    LEFT JOIN accounts a ON m.account_id = a.id
    WHERE a.auth_key = '{$key}'
SQL;
  if (!empty($id))
  {
    $query .= <<<SQL
      AND m.id = '{$id}'
SQL;
  }
  if (!empty($quantity) && is_numeric($quantity))
  {
    $query .= <<<SQL
      ORDER BY timestamp DESC LIMIT {$quantity}
SQL;
  }
  $result = mysqli_query($connect, $query) or die(mysqli_error($connect));
  while ($row = $result->fetch_assoc()) {
    $assoc[] = $row;
  }

  $response->getBody()->write(json_encode($assoc));

  $response->header('Access-Control-Allow-Origin', '*');
  return $response->withStatus($status);
});

$app->options('/memoapi/login', function (Request $request, Response $response) {
  $response->header('Access-Control-Allow-Origin', '*');
});

$app->post('/memoapi/login', function (Request $request, Response $response) {
  $connect = mysqli_connect(Config::dbHost, Config::dbUser, Config::dbPass);
  mysqli_select_db($connect, Config::dbName);

  $badge = mysqli_real_escape_string($connect, $request->getHeader('badge')[0]);
  $pin = mysqli_real_escape_string($connect, $request->getHeader('pin')[0]);

$response->getBody().write(var_export($request, true));
$response->header('Access-Control-Allow-Origin', '*');
return $response;
  if (!empty($badge) && !empty($pin))
  {
    $md5 = md5($badge . $pin);

    $query = <<<SQL
      SELECT *
      FROM accounts
      WHERE badge = '{$badge}'
SQL;

    $result = mysqli_query($connect, $query) or die(mysqli_error($connect));
    $assoc = mysqli_fetch_assoc($result);
    if (!empty($assoc))
    {
      $response->getBody()->write($assoc['auth_key']);
      $status = 200;
    }
    else
    {
      $query = <<<SQL
        INSERT INTO accounts (badge, auth_key)
        VALUES ('{$badge}', '{$md5}')
SQL;
      $result = mysqli_query($connect, $query) or die(mysqli_error($connect));
      if ($result)
      {
        $query = <<<SQL
          SELECT *
          FROM accounts
          WHERE badge = '{$badge}'
SQL;
        $result = mysqli_query($connect, $query) or die(mysqli_error($connect));
        $assoc = mysqli_fetch_assoc($result);
        $response->getBody()->write($assoc['auth_key']);
        $status = 200;
      }
    }
  }
  else {
    if (empty($badge))
    {
      $response->getBody()->write('badge cannot be empty' . PHP_EOL);
    }
    if (empty($pin))
    {
      $response->getBody()->write('pin cannot be empty');
    }
    $status = 400;
  }

  $response->header('Access-Control-Allow-Origin', '*');
  return $response->withStatus($status);
});

$connect = mysqli_connect(Config::dbHost, Config::dbUser, Config::dbPass);
mysqli_select_db($connect, Config::dbName);
MakeMemoTable($connect);
MakeAccountTable($connect);

$app->run();
