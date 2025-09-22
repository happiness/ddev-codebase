<?php
#ddev-generated
#ddev-description: Class for interaction with the Codebase API.

/**
 * Codebase API gateway.
 */
class CodebaseAPI {

  protected const string TEMP_DIR = '/tmp/ddev-codebase';
  protected string $baseUrl;

  public function __construct(
    protected string $apiKey,
    protected string $apiUrl,
    protected string $project,
    protected string $username
  ) {
    $url = parse_url($this->apiUrl);
    $username = urlencode($this->username);;
    $this->baseUrl = "https://{$username}:{$this->apiKey}@{$url['host']}";
  }

  public function test() {
    $data = [
      'api_key' => $this->apiKey,
      'api_url' => $this->apiUrl,
      'project' => $this->project,
      'username' => $this->username,
      'test' => 'test',
    ];
    return $data;
  }

  /**
   * Print a list of tickets.
   */
  public function tickets() {
    return $this->getTickets();
  }

  /**
   * Get a list of tickets.
   */
  protected function getTickets(): array {
    $account = strtok($this->username, '/');
    $username = strtok('/');
    $xml = $this->getRequest('/tickets?query=status:open+assignee:' . $username);

    $doc = new DOMDocument();
    $doc->loadXML($xml);
    $rows = [];
    $root = $doc->documentElement;
    /** @var \DOMElement $item */
    foreach ($root->getElementsByTagName('ticket') AS $item) {
      $id = (int) $item->getElementsByTagName('ticket-id')->item(0)->nodeValue;
      $row = $this->getTicket($id);
      $rows['tickets'][] = (object) $row;
    }
    return $rows;
  }

  /**
   * Print a ticket.
   */
  public function ticket(int $id) {
    return $this->getTicket($id);
  }

  /**
   * Get a ticket.
   */
  protected function getTicket(int $id): array {
    $xml = $this->getRequest('/tickets/' . $id);
    $doc = new DOMDocument();
    $doc->loadXML($xml);
    $root = $doc->documentElement;
    return [
      'id' => $id,
      'summary' => $root->getElementsByTagName('summary')->item(0)->nodeValue,
      'updated' => $root->getElementsByTagName('updated-at')->item(0)->nodeValue,
      'created' => $root->getElementsByTagName('created-at')->item(0)->nodeValue,
      'issue_url' => 'https://code.happiness.se/projects/ki-profile/tickets/' . $id,
    ];
  }

  /**
   * Perform a request to the API.
   */
  protected function getRequest(string $method): string {
    // Check if a cache file exists and is not older than 5 minutes.
    $cache_file = $this->getCacheFilename($method);
    if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 60 * 5 ))) {
      $xml = file_get_contents($cache_file);
      return $xml;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "{$this->baseUrl}/{$this->project}{$method}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Accept: application/xml',
      'Content-type: application/xml',
    ]);

    $response = curl_exec($ch);
    $error = false;
    if (curl_errno($ch)) {
      $error = curl_error($ch);
    }
    curl_close($ch);

    if ($error) {
      throw new \Exception($error);
    }

    // Write the XML response to cache.
    @mkdir(self::TEMP_DIR, 0775);
    file_put_contents($cache_file, $response, LOCK_EX);

    return $response;
  }

  /**
   * Get cache filename.
   */
  protected function getCacheFilename(string $string): string {
    return self::TEMP_DIR . '/' . preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $string);
  }

}
