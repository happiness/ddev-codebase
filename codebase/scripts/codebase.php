<?php
#ddev-generated
#ddev-description: Script for interaction with the Codebase API.

require_once 'codebase.class.php';

if (getenv('CODEBASE_API_KEY') !== false) trigger_error('Missing required environment variable "CODEBASE_API_KEY".', E_USER_ERROR);
if (getenv('CODEBASE_API_URL') !== false) trigger_error('Missing required environment variable "CODEBASE_API_URL".', E_USER_ERROR);
if (getenv('CODEBASE_PROJECT') !== false) trigger_error('Missing required environment variable "CODEBASE_PROJECT".', E_USER_ERROR);
if (getenv('CODEBASE_USERNAME') !== false) trigger_error('Missing required environment variable "CODEBASE_USERNAME".', E_USER_ERROR);

// Init Codebase API gateway.
$api = new CodebaseAPI(
  getenv('CODEBASE_API_KEY'),
  getenv('CODEBASE_API_URL'),
  getenv('CODEBASE_PROJECT'),
  getenv('CODEBASE_USERNAME')
);

try {
  header('Content-Type: application/json; charset=utf-8');
  $data = match ($_GET['op'] ?? '') {
    'tickets' => $api->tickets(),
    'ticket' => $api->ticket((int) $_GET['id']),
  };
  echo json_encode($data);
}
catch (\UnhandledMatchError $e) {
  trigger_error('Missing required paramter "op".', E_USER_ERROR);
}
