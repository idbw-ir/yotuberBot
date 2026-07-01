<?php

declare(strict_types=1);

namespace App\Core;

use Exception;

class DatabaseBunny {
    private string $url;
    private string $token;

    public function __construct(string $url, string $token) {
        $this->url = rtrim(str_replace('libsql://', 'https://', $url), '/');
        $this->token = $token;
    }

    public function execute(string $sql, array $params = []): array {
        $body = ['stmt' => ['sql' => $sql]];
        if (!empty($params)) {
            $body['stmt']['args'] = [];
            foreach ($params as $p) {
                $body['stmt']['args'][] = $this->toTursoArg($p);
            }
        }

        $ch = curl_init($this->url . '/v1/execute');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($body),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception('Bunny Database connection error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $detail = '';
            if ($response) {
                $err = json_decode($response, true);
                $detail = $err['error']['message'] ?? $err['message'] ?? $response;
            }
            throw new Exception("Bunny Database HTTP {$httpCode}: {$detail}");
        }

        return json_decode($response, true) ?? [];
    }

    private function toTursoArg(mixed $value): array {
        if (is_null($value)) {
            return ['type' => 'null', 'value' => null];
        }
        if (is_int($value)) {
            return ['type' => 'integer', 'value' => $value];
        }
        if (is_float($value)) {
            return ['type' => 'float', 'value' => $value];
        }
        if (is_bool($value)) {
            return ['type' => 'integer', 'value' => $value ? 1 : 0];
        }
        return ['type' => 'text', 'value' => (string)$value];
    }

    public function query(string $sql, array $params = []): array {
        $result = $this->execute($sql, $params);
        $rows = $result['results']['rows'] ?? [];
        $cols = $result['results']['columns'] ?? [];
        $out = [];
        foreach ($rows as $row) {
            $assoc = [];
            foreach ($row as $i => $val) {
                $assoc[$cols[$i] ?? $i] = $val;
            }
            $out[] = $assoc;
        }
        return $out;
    }

    public function fetch(string $sql, array $params = []): ?array {
        $rows = $this->query($sql, $params);
        return $rows[0] ?? null;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params);
    }

    public function fetchColumn(string $sql, array $params = []): mixed {
        $row = $this->fetch($sql, $params);
        if ($row) {
            return reset($row);
        }
        return null;
    }

    public function insert(string $table, array $data): ?int {
        $cols = implode(', ', array_keys($data));
        $phs = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$cols}) VALUES ({$phs})";
        $result = $this->execute($sql, array_values($data));

        $lastId = $result['results']['last_insert_rowid'] ?? null;
        return $lastId !== null ? (int)$lastId : null;
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = [];
        $params = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :set_{$key}";
            $params[":set_{$key}"] = $value;
        }
        $setStr = implode(', ', $set);
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$where}";
        $allParams = array_merge($params, $whereParams);
        $result = $this->execute($sql, array_values($allParams));
        return $result['results']['affected_row_count'] ?? 0;
    }

    public function delete(string $table, string $where, array $params = []): int {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $result = $this->execute($sql, $params);
        return $result['results']['affected_row_count'] ?? 0;
    }

    public function count(string $table, string $where = '1', array $params = []): int {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $row = $this->fetch($sql, $params);
        return $row ? (int)$row['count'] : 0;
    }

    public function exists(string $table, string $where, array $params = []): bool {
        return $this->count($table, $where, $params) > 0;
    }

    public function tableExists(string $table): bool {
        $tables = $this->query("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$table]);
        return !empty($tables);
    }

    public function getTables(): array {
        $rows = $this->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
        return array_map(fn($r) => $r['name'], $rows);
    }
}
