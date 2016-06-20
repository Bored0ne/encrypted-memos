<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description, key');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'config.php';

$app = new \Slim\App;
unset($app->getContainer()['errorHandler']);

function MakeMemoTable($connect)
{
  $query = <<<SQL
    CREATE TABLE IF NOT EXISTS `memos`.`memos` (
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
      `auth_key` VARCHAR(60) NOT NULL COMMENT '',
      PRIMARY KEY (`id`)  COMMENT '',
      UNIQUE INDEX `id_UNIQUE` (`id` ASC)  COMMENT '',
      UNIQUE INDEX `badge_UNIQUE` (`badge` ASC)  COMMENT '');
SQL;
  mysqli_query($connect, $query) or die(mysqli_error($connect));
}

$app->delete('/memoapi/memos/{id}', function ($request, $response, $args)
{
  $status = 200;
  $connect = mysqli_connect(Config::dbHost, Config::dbUser, Config::dbPass);
  mysqli_select_db($connect, Config::dbName);

  $id = mysqli_real_escape_string($connect, $args['id']);
  $key = mysqli_real_escape_string($connect, $request->getHeader('key')[0]);

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
  $result = mysqli_query($connect, $query) OR $status = 500;

  return $response->withStatus($status);
});

$app->map(['PUT', 'POST'], '/memoapi/memos[/{id}]', function($request, $response, $args){
  $status = 200;
  $connect = mysqli_connect(Config::dbHost, Config::dbUser, Config::dbPass);
  mysqli_select_db($connect, Config::dbName);

  $body = json_decode($request->getBody(), true);

  $key = mysqli_real_escape_string($connect, $body['key']);
  $memo = mysqli_real_escape_string($connect, $body['memo']);
  @$id = mysqli_real_escape_string($connect, $args['id']);

  $query = <<<SQL
    SELECT id
    FROM accounts
    WHERE auth_key = '{$key}'
SQL;
  $result = mysqli_query($connect, $query) OR $response->getBody()->write('key not associated with account' . PHP_EOL);
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
    }
    else
    {
      $query = <<<SQL
        INSERT INTO memos (memo, account_id, timestamp)
        VALUES ('{$memo}', '{$accountId}', CURRENT_TIMESTAMP())
SQL;
    }
    mysqli_query($connect, $query) OR $status = 500;
  }

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
  $assoc = array();
  while ($row = mysqli_fetch_assoc($result)) {
    $assoc[] = $row;
  }
  if(!empty($assoc))
  {
    $response->getBody()->write(json_encode($assoc));
  }
  else
  {
    $response->getBody()->write('{}');
  }


  return $response->withStatus($status);
});

$app->post('/memoapi/login', function (Request $request, Response $response) {
  $connect = mysqli_connect(Config::dbHost, Config::dbUser, Config::dbPass);
  mysqli_select_db($connect, Config::dbName);
  $body = json_decode($request->getBody(), true);
  $badge = mysqli_real_escape_string($connect, $body['badge']);
  $pin = mysqli_real_escape_string($connect, $body['pin']);

  if (!empty($badge) && !empty($pin))
  {
    $str = $badge . $pin;
    $md5 = sha1($str);

    $query = <<<SQL
      SELECT *
      FROM accounts
      WHERE badge = '{$badge}'
SQL;

    $result = mysqli_query($connect, $query);
    $assoc = mysqli_fetch_assoc($result);
    if (!empty($assoc))
    {
      if ($md5 !== $assoc['auth_key']){
        $status = 401;
        $response->getBody()->write($assoc['auth_key'] . ' ' . $md5 . ' ');
        $response->getBody()->write('Bad Username/Password Combo');
        return $response->withStatus($status);
      }
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
  return $response->withStatus($status);
});

$connect = mysqli_connect(Config::dbHost, Config::dbUser, Config::dbPass);
mysqli_select_db($connect, Config::dbName);
MakeMemoTable($connect);
MakeAccountTable($connect);

$app->run();
