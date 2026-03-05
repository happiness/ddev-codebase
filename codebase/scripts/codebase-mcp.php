<?php
#ddev-generated
#ddev-description: Script for interaction with the Codebase API.

require_once 'CodebaseMCPServer.class.php';

if (getenv('CODEBASE_API_KEY') === false) trigger_error('Missing required environment variable "CODEBASE_API_KEY".', E_USER_ERROR);
if (getenv('CODEBASE_API_URL') === false) trigger_error('Missing required environment variable "CODEBASE_API_URL".', E_USER_ERROR);
if (getenv('CODEBASE_PROJECT') === false) trigger_error('Missing required environment variable "CODEBASE_PROJECT".', E_USER_ERROR);
if (getenv('CODEBASE_USERNAME') === false) trigger_error('Missing required environment variable "CODEBASE_USERNAME".', E_USER_ERROR);

// Init Codebase API gateway.
$server = new CodebaseMCPServer(
  getenv('CODEBASE_USERNAME'),
  getenv('CODEBASE_API_KEY'),
  getenv('CODEBASE_PROJECT'),
);


try {
  header('Content-Type: application/json; charset=utf-8');
  $data = $server->handleRequest($_GET);
  echo json_encode($data);
}
catch (Exception $e) {
  echo json_encode(['error' => $e->getMessage()]);
}

