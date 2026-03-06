<?php
#ddev-generated
#ddev-description: Class for interaction with the Codebase API via MCP protocol.

class CodebaseMCPServer {
  private string $baseUrl;

  public function __construct(
    private string $username,
    private string $apiKey,
    private string $project
  ) {
    // Codebase API usually uses Basic Auth: username/token:apikey
    // Using the API documentation format for the base URL
    $this->baseUrl = "https://api3.codebasehq.com/{$this->project}";
  }

  /**
   * Main loop to handle MCP requests from stdin and respond to stdout.
   */
  public function run(): void {
    $stdin = fopen('php://stdin', 'r');
    while ($line = fgets($stdin)) {
      $request = json_decode($line, true);
      if (!$request) continue;

      $response = $this->handleRequest($request);
      echo json_encode($response) . "\n";
    }
  }

  private function handleRequest(array $request): array {
    $method = $request['method'] ?? '';
    $params = $request['params'] ?? [];
    $id = $request['id'] ?? null;

    try {
      $result = match ($method) {
        'initialize' => $this->initialize(),
        'tools/list' => $this->listTools(),
        'tools/call' => $this->callTool($params['name'] ?? '', $params['arguments'] ?? []),
        'resources/list' => $this->listResources(),
        'resources/read' => $this->readResource($params['uri'] ?? ''),
        default => throw new Exception("Method not found: $method"),
      };

      return [
        'jsonrpc' => '2.0',
        'id' => $id,
        'result' => $result
      ];
    } catch (Exception $e) {
      return [
        'jsonrpc' => '2.0',
        'id' => $id,
        'error' => ['code' => -32603, 'message' => $e->getMessage()]
      ];
    }
  }

  private function initialize(): array {
    return [
      'protocolVersion' => '2024-11-05',
      'capabilities' => [
        'tools' => (object)[],
        'resources' => (object)[],
      ],
      'serverInfo' => [
        'name' => 'codebase-hq-mcp-server',
        'version' => '1.0.0'
      ]
    ];
  }

  private function listTools(): array {
    return [
      'tools' => [
        [
          'name' => 'list_tickets',
          'description' => 'List open tickets in the project',
          'inputSchema' => [
            'type' => 'object',
            'properties' => [
              'query' => ['type' => 'string', 'description' => 'Search query (e.g., status:open, assignee:username, priority:high).']
            ]
          ]
        ],
        [
          'name' => 'get_ticket',
          'description' => 'Get details of a specific ticket',
          'inputSchema' => [
            'type' => 'object',
            'properties' => [
              'ticket_id' => ['type' => 'integer']
            ],
            'required' => ['ticket_id']
          ]
        ],
        [
          'name' => 'get_ticket_notes',
          'description' => 'Get notes (comments) for a specific ticket',
          'inputSchema' => [
            'type' => 'object',
            'properties' => [
              'ticket_id' => ['type' => 'integer', 'description' => 'The ID of the ticket']
            ],
            'required' => ['ticket_id']
          ]
        ],
        [
          'name' => 'get_ticket_statuses',
          'description' => 'Get all ticket statuses for the project',
          'inputSchema' => [
            'type' => 'object',
            'properties' => (object)[]
          ]
        ],
        [
          'name' => 'get_ticket_priorities',
          'description' => 'Get all ticket priorities for the project',
          'inputSchema' => [
            'type' => 'object',
            'properties' => (object)[]
          ]
        ],
        [
          'name' => 'get_ticket_categories',
          'description' => 'Get all ticket categories for the project',
          'inputSchema' => [
            'type' => 'object',
            'properties' => (object)[]
          ]
        ],
        [
          'name' => 'get_ticket_types',
          'description' => 'Get all ticket types for the project',
          'inputSchema' => [
            'type' => 'object',
            'properties' => (object)[]
          ]
        ],
        [
          'name' => 'create_ticket',
          'description' => 'Create a new ticket in the project',
          'inputSchema' => [
            'type' => 'object',
            'properties' => [
              'summary' => ['type' => 'string', 'description' => 'The title of the ticket'],
              'description' => ['type' => 'string', 'description' => 'The detailed description of the ticket'],
              'status' => ['type' => 'string', 'description' => 'Status name (e.g., New, Open)'],
              'priority' => ['type' => 'string', 'description' => 'Priority name (e.g., Low, Normal, High)'],
              'category' => ['type' => 'string', 'description' => 'Category name'],
              'assignee' => ['type' => 'string', 'description' => 'Username of the assignee'],
            ],
            'required' => ['summary']
          ]
        ],
        [
          'name' => 'update_ticket',
          'description' => 'Update a ticket (add a note, change status, etc.)',
          'inputSchema' => [
            'type' => 'object',
            'properties' => [
              'ticket_id' => ['type' => 'integer', 'description' => 'The ID of the ticket to update'],
              'content' => ['type' => 'string', 'description' => 'The comment or note text'],
              'status' => ['type' => 'string', 'description' => 'New status name'],
              'priority' => ['type' => 'string', 'description' => 'New priority name'],
              'category' => ['type' => 'string', 'description' => 'New category name'],
              'assignee' => ['type' => 'string', 'description' => 'Username of the new assignee'],
              'summary' => ['type' => 'string', 'description' => 'New summary/title'],
            ],
            'required' => ['ticket_id']
          ]
        ],
        [
          'name' => 'get_milestones',
          'description' => 'Get all milestones for the project',
          'inputSchema' => [
            'type' => 'object',
            'properties' => (object)[]
          ]
        ],
        [
          'name' => 'get_project_activity',
          'description' => 'Get the recent activity for the project',
          'inputSchema' => [
            'type' => 'object',
            'properties' => (object)[]
          ]
        ],
      ]
    ];
  }

  private function callTool(string $name, array $args): array {
    $content = match ($name) {
      'list_tickets' => $this->apiGet("/tickets", ['query' => $args['query'] ?? 'status:open']),
      'get_ticket' => $this->apiGet("/tickets/{$args['ticket_id']}"),
      'get_ticket_notes' => $this->apiGet("/tickets/{$args['ticket_id']}/notes"),
      'get_ticket_statuses' => $this->apiGet("/tickets/statuses"), // Added
      'get_ticket_priorities' => $this->apiGet("/tickets/priorities"), // Added
      'get_ticket_categories' => $this->apiGet("/tickets/categories"), // Added
      'get_ticket_types' => $this->apiGet("/tickets/types"), // Added
      'create_ticket' => $this->apiPost("/tickets", ['ticket' => $args]),
      'update_ticket' => $this->apiPost("/tickets/{$args['ticket_id']}/notes", ['ticket_note' => $args]),
      'get_milestones' => $this->apiGet("/milestones"),
      'get_project_activity' => $this->apiGet("/activity"),
      default => throw new Exception("Unknown tool: $name"),
    };

    return [
      'content' => [
        ['type' => 'text', 'text' => json_encode($content, JSON_PRETTY_PRINT)]
      ]
    ];
  }

  private function listResources(): array {
    return [
      'resources' => [
        [
          'uri' => 'docs://tickets/search',
          'name' => 'Ticket Search Guide',
          'description' => 'Information on how to perform ticket search in Codebase',
          'mimeType' => 'text/html'
        ]
      ]
    ];
  }

  private function readResource(string $uri): array {
    if ($uri === 'docs://tickets/search') {
      return [
        'contents' => [
          [
            'uri' => $uri,
            'mimeType' => 'text/html',
            'text' => "For detailed information on ticket search, visit: https://support.codebasehq.com/articles/tickets/quick-search\n\nCommon search terms:\n- `status:open` - Show only open tickets\n- `assignee:username` - Search by assignee\n- `priority:high` - Search by priority"
          ]
        ]
      ];
    }
    throw new Exception("Resource not found: $uri");
  }

  private function apiGet(string $path, array $params = []): array {
    $url = $this->baseUrl . $path . '.json';
    if ($params) {
      $url .= '?' . http_build_query($params);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->apiKey}");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($status >= 400) {
      throw new Exception("Codebase API error ($status): " . $response);
    }

    curl_close($ch);
    return json_decode($response, true) ?? [];
  }

  private function apiPost(string $path, array $data): array {
    $url = $this->baseUrl . $path . '.json';
    $ch = curl_init($url);
    $payload = json_encode($data);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->apiKey}");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Accept: application/json',
      'Content-Type: application/json',
      'Content-Length: ' . strlen($payload)
    ]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($status >= 400) {
      throw new Exception("Codebase API error ($status): " . $response);
    }

    curl_close($ch);
    return json_decode($response, true) ?? [];
  }

}

// Usage (CLI entry point)
if (php_sapi_name() === 'cli') {
  if (getenv('CODEBASE_API_KEY') === false) trigger_error('Missing required environment variable "CODEBASE_API_KEY".', E_USER_ERROR);
  if (getenv('CODEBASE_API_URL') === false) trigger_error('Missing required environment variable "CODEBASE_API_URL".', E_USER_ERROR);
  if (getenv('CODEBASE_PROJECT') === false) trigger_error('Missing required environment variable "CODEBASE_PROJECT".', E_USER_ERROR);
  if (getenv('CODEBASE_USERNAME') === false) trigger_error('Missing required environment variable "CODEBASE_USERNAME".', E_USER_ERROR);

  $server = new CodebaseMCPServer(
    getenv('CODEBASE_USERNAME') ?: 'user/account',
    getenv('CODEBASE_API_KEY') ?: '',
    getenv('CODEBASE_PROJECT') ?: ''
  );
  $server->run();
}
