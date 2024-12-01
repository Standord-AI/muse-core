<?php

namespace Kaviru\MuseCore;

use InvalidArgumentException;
use Kaviru\MuseCore\DataHandling;
use Kaviru\MuseCore\ErrorHandling;
use PDO;
use PDOException;

abstract class Database
{
    private $dbConnection;
    private $servername;
    private $username;
    private $password;
    private $port;
    private $dbName;
    private $charset;
    private $dsn;
    private $fetchMode;

    protected $connection;
    protected $table;
    protected $fillable = [];
    protected $readable = [];
    protected $dataTypes = [];
    private $sortOrder = "";

    public function __construct()
    {
        $data = new DataHandling();
        $this->dbConnection = $data->env->DB_CONNECTION ?? "mysql";
        $this->servername = $data->env->DB_HOST ?? "localhost";
        $this->port = $data->env->DB_PORT ?? 3306;
        $this->dbName = $data->env->DB_DATABASE ?? "";
        $this->charset = $data->env->DB_CHARSET ?? "utf8mb4";
        $this->username = $data->env->DB_USERNAME ?? "root";
        $this->password = $data->env->DB_PASSWORD ?? "";
        $this->fetchMode = PDO::FETCH_ASSOC;

        // Set up the PDO connection
        $this->dsn = "$this->dbConnection:host=$this->servername;port=$this->port;dbname=$this->dbName;charset=$this->charset";

        try {
            $this->connection = new PDO($this->dsn, $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            //die("Database connection failed: " . $e->getMessage());
            ErrorHandling::_500("Database connection failed: " . $e->getMessage());
        }
    }

    private function getPdoParamType($type)
    {
        switch (strtolower($type)) {
            case 'int':
                return PDO::PARAM_INT;
            case 'string':
            case 'text':
                return PDO::PARAM_STR;
            case 'bool':
                return PDO::PARAM_BOOL;
            case 'null':
                return PDO::PARAM_NULL;
            default:
                throw new InvalidArgumentException("Unsupported data type: $type");
        }
    }

    public function create(array $data)
    {
        $data = array_intersect_key($data, array_flip($this->fillable));

        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->connection->prepare($sql);

        foreach ($data as $key => $value) {
            $type = $this->dataTypes[$key] ?? 'string';
            $stmt->bindValue(":$key", $value, $this->getPdoParamType($type));
        }

        return $stmt->execute();
    }

    public function save($id, array $data)
    {
        $data = array_intersect_key($data, array_flip($this->fillable));

        $setClause = "";
        foreach ($data as $key => $value) {
            $setClause .= "$key = :$key, ";
        }
        $setClause = rtrim($setClause, ", ");

        $sql = "UPDATE {$this->table} SET $setClause WHERE id = :id";
        $stmt = $this->connection->prepare($sql);

        foreach ($data as $key => $value) {
            $type = $this->dataTypes[$key] ?? 'string';
            $stmt->bindValue(":$key", $value, $this->getPdoParamType($type));
        }
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function get($id)
    {
        $columns = implode(", ", $this->readable);
        $sql = "SELECT $columns FROM {$this->table} WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetch($this->fetchMode);
    }

    public function where(array $conditions, $logicalOperator = 'AND')
    {
        $clauses = [];
        $params = [];

        foreach ($conditions as $condition) {
            if (count($condition) !== 3) {
                throw new InvalidArgumentException("Each condition must be an array with 3 elements: column, operator, value.");
            }
            [$column, $operator, $value] = $condition;

            if (!in_array($operator, ['=', '!=', '>', '>=', '<', '<='], true)) {
                throw new InvalidArgumentException("Unsupported operator: $operator");
            }

            $paramName = ":$column" . count($params);
            $clauses[] = "$column $operator $paramName";
            $params[$paramName] = [
                'value' => $value,
                'type' => $this->getPdoParamType($this->dataTypes[$column] ?? 'string'),
            ];
        }

        $whereClause = implode(" $logicalOperator ", $clauses);
        $columns = implode(", ", $this->readable);

        $sql = "SELECT $columns FROM {$this->table} WHERE $whereClause {$this->sortOrder}";
        $stmt = $this->connection->prepare($sql);

        foreach ($params as $paramName => $param) {
            $stmt->bindValue($paramName, $param['value'], $param['type']);
        }

        $stmt->execute();
        return $stmt->fetchAll($this->fetchMode);
    }

    public function sort($order = 'ASC', $column = 'id')
    {
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'], true)) {
            throw new InvalidArgumentException("Invalid sort order: $order");
        }
        $this->sortOrder = "ORDER BY $column $order";
        return $this;
    }
}
