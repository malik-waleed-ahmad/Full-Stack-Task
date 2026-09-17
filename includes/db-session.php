<?php

require_once __DIR__ . '/../config/database.php';

class DatabaseSessionHandler implements SessionHandlerInterface
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  public function open(string $path, string $name): bool
  {
    return true;
  }

  public function close(): bool
  {
    return true;
  }

  public function read(string $id): string|false
  {
    $stmt = $this->pdo->prepare(
      "SELECT data FROM sessions WHERE id = ?"
    );

    $stmt->execute([$id]);

    $data = $stmt->fetchColumn();

    return $data !== false ? $data : '';
  }

  public function write(string $id, string $data): bool
  {
    $stmt = $this->pdo->prepare(
      "INSERT INTO sessions (id, data, last_activity)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE
                data = VALUES(data),
                last_activity = VALUES(last_activity)"
    );

    return $stmt->execute([
      $id,
      $data,
      time()
    ]);
  }

  public function destroy(string $id): bool
  {
    $stmt = $this->pdo->prepare(
      "DELETE FROM sessions WHERE id = ?"
    );

    return $stmt->execute([$id]);
  }

  public function gc(int $max_lifetime): int|false
  {
    $stmt = $this->pdo->prepare(
      "DELETE FROM sessions
             WHERE last_activity < ?"
    );

    $stmt->execute([
      time() - $max_lifetime
    ]);

    return $stmt->rowCount();
  }
}

if (session_status() === PHP_SESSION_NONE) {

  $handler = new DatabaseSessionHandler($pdo);

  session_set_save_handler(
    $handler,
    true
  );

  session_start();
}
