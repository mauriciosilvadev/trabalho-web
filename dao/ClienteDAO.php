<?php

require_once __DIR__ . '/../config/db.php';

/**
 * Data Access Object for clients
 */
class ClienteDAO
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all clients
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nome, cpf, cidade, email, telefone, endereco, created_at 
                FROM clientes 
                ORDER BY nome
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching all clients: " . $e->getMessage());
            return [];
        }
    }

    public function listarTodos()
    {
        $sql = "SELECT * FROM clientes ORDER BY nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get all clients (alias for findAll)
     */
    public function getAll($search = '', $page = 1, $perPage = 15): array
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT id, nome, cpf, cidade, email, telefone, endereco, created_at 
                    FROM clientes";
            $params = [];

            if (!empty($search)) {
                $sql .= " WHERE nome LIKE ? OR email LIKE ? OR cpf LIKE ? OR cidade LIKE ?";
                $searchParam = "%{$search}%";
                $params = [$searchParam, $searchParam, $searchParam, $searchParam];
            }

            $sql .= " ORDER BY nome";

            // Only add pagination if page is specified
            if ($page > 0 && $perPage > 0) {
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = $perPage;
                $params[] = $offset;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting all clients: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find client by ID
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nome, cpf, cidade, email, telefone, endereco, created_at 
                FROM clientes 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $client = $stmt->fetch();
            return $client ?: null;
        } catch (PDOException $e) {
            error_log("Error finding client by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new client
     */
    public function create(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO clientes (nome, cpf, cidade, email, telefone, endereco) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $result = $stmt->execute([
                $data['nome'],
                $data['cpf'],
                $data['cidade'],
                $data['email'],
                $data['telefone'] ?? null,
                $data['endereco'] ?? null
            ]);

            return $result ? $this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("Error creating client: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update client
     */
    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE clientes 
                SET nome = ?, cpf = ?, cidade = ?, email = ?, telefone = ?, endereco = ?
                WHERE id = ?
            ");

            return $stmt->execute([
                $data['nome'],
                $data['cpf'],
                $data['cidade'],
                $data['email'],
                $data['telefone'] ?? null,
                $data['endereco'] ?? null,
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating client: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete client
     */
    public function delete(int $id): bool
    {
        try {
            // Check if client has contracts
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM contratacoes WHERE cliente_id = ?");
            $stmt->execute([$id]);

            if ($stmt->fetchColumn() > 0) {
                error_log("Cannot delete client with existing contracts");
                return false;
            }

            $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting client: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if CPF exists
     */
    public function cpfExists(string $cpf, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM clientes WHERE cpf = ?";
            $params = [$cpf];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking CPF existence: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM clientes WHERE email = ?";
            $params = [$email];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking email existence: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search clients by filters
     */
    public function search(array $filters): array
    {
        try {
            $sql = "SELECT id, nome, cpf, cidade, email, telefone, endereco, created_at FROM clientes WHERE 1=1";
            $params = [];

            if (!empty($filters['nome'])) {
                $sql .= " AND nome LIKE ?";
                $params[] = '%' . $filters['nome'] . '%';
            }

            if (!empty($filters['cpf'])) {
                $sql .= " AND cpf LIKE ?";
                $params[] = '%' . $filters['cpf'] . '%';
            }

            if (!empty($filters['cidade'])) {
                $sql .= " AND cidade LIKE ?";
                $params[] = '%' . $filters['cidade'] . '%';
            }

            if (!empty($filters['email'])) {
                $sql .= " AND email LIKE ?";
                $params[] = '%' . $filters['email'] . '%';
            }

            $sql .= " ORDER BY nome";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error searching clients: " . $e->getMessage());
            return [];
        }
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM clientes WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function inserir($nome, $cpf, $cidade, $email, $telefone = null, $endereco = null)
    {
        $sql = "INSERT INTO clientes (nome, cpf, cidade, email, telefone, endereco) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$nome, $cpf, $cidade, $email, $telefone, $endereco]);
    }

    public function atualizar($id, $nome, $cpf, $cidade, $email, $telefone = null, $endereco = null)
    {
        $sql = "UPDATE clientes SET nome = ?, cpf = ?, cidade = ?, email = ?, telefone = ?, endereco = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$nome, $cpf, $cidade, $email, $telefone, $endereco, $id]);
    }

    public function excluir($id)
    {
        $sql = "DELETE FROM clientes WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }

    public function cpfExiste($cpf, $excluirId = null)
    {
        $sql = "SELECT COUNT(*) FROM clientes WHERE cpf = ?";
        $params = [$cpf];

        if ($excluirId) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

    public function emailExiste($email, $excluirId = null)
    {
        $sql = "SELECT COUNT(*) FROM clientes WHERE email = ?";
        $params = [$email];

        if ($excluirId) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

    public function buscarPorCPF($cpf)
    {
        $sql = "SELECT * FROM clientes WHERE cpf = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cpf]);

        return $stmt->fetch();
    }

    public function temContratacoes($id)
    {
        $sql = "SELECT COUNT(*) FROM contratacoes WHERE cliente_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetchColumn() > 0;
    }
}
