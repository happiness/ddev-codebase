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

  public function handleRequest(array $request): array {
    $method = $request['method'] ?? '';
    $params = $request['params'] ?? [];
    $id = $request['id'] ?? null;

    try {
      $result = match ($method) {
        'initialize' => $this->initialize(),
        'list_tools' => $this->listTools(),
        'call_tool' => $this->callTool($params['name'] ?? '', $params['arguments'] ?? []),
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
        'tools' => (object)[]
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
              'query' => ['type' => 'string', 'description' => 'Search query (e.g., status:open)']
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
        ]
      ]
    ];
  }

  private function callTool(string $name, array $args): array {
    $content = match ($name) {
      'list_tickets' => $this->apiGet("/tickets", ['query' => $args['query'] ?? 'status:open']),
      'get_ticket' => $this->apiGet("/tickets/{$args['ticket_id']}"),
      default => throw new Exception("Unknown tool: $name"),
    };

    return [
      'content' => [
        ['type' => 'text', 'text' => json_encode($content, JSON_PRETTY_PRINT)]
      ]
    ];
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

}
