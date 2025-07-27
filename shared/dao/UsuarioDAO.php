<?php

require_once __DIR__ . '/../config/db.php';

/**
 * Data Access Object for users
 */
class UsuarioDAO
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find user by login credentials
     */
    public function findByCredentials(string $login, string $password): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['senha'])) {
                unset($user['senha']); // Remove password from returned data
                return $user;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error finding user by credentials: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by remember token
     */
    public function findByRememberToken(string $hashedToken): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE remember_token = ?");
            $stmt->execute([$hashedToken]);
            $user = $stmt->fetch();

            if ($user) {
                unset($user['senha']); // Remove password from returned data
                return $user;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error finding user by remember token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update remember token for user
     */
    public function updateRememberToken(int $userId, string $hashedToken): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
            return $stmt->execute([$hashedToken, $userId]);
        } catch (PDOException $e) {
            error_log("Error updating remember token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear remember token for user
     */
    public function clearRememberToken(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET remember_token = NULL WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Error clearing remember token: " . $e->getMessage());
            return false;
        }
    }

    public function autenticar($email, $password)
    {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);

        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['senha'])) {
            return $usuario;
        }

        return null;
    }

    public function buscarPorToken($token)
    {
        $sql = "SELECT * FROM usuarios WHERE remember_token = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);

        return $stmt->fetch();
    }

    public function salvarRememberToken($userId, $token)
    {
        $sql = "UPDATE usuarios SET remember_token = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$token, $userId]);
    }

    public function listarTodos()
    {
        $sql = "SELECT id, nome, email, tipo, created_at FROM usuarios ORDER BY nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function inserir($nome, $email, $senha, $tipo = 'operador')
    {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$nome, $email, $senhaHash, $tipo]);
    }

    public function atualizar($id, $nome, $email, $tipo)
    {
        $sql = "UPDATE usuarios SET nome = ?, email = ?, tipo = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$nome, $email, $tipo, $id]);
    }

    public function atualizarSenha($id, $novaSenha)
    {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$senhaHash, $id]);
    }

    public function excluir($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }

    public function emailExiste($email, $excluirId = null)
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
        $params = [$email];

        if ($excluirId) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get all users with search and pagination
     */
    public function getAll($search = '', $page = 1, $perPage = 15): array
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT id, nome, login, email, tipo, ativo, criado_em, ultimo_acesso 
                    FROM usuarios";
            $params = [];

            if (!empty($search)) {
                $sql .= " WHERE nome LIKE ? OR email LIKE ? OR login LIKE ?";
                $searchParam = "%{$search}%";
                $params = [$searchParam, $searchParam, $searchParam];
            }

            $sql .= " ORDER BY nome LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting all users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count users with search filter
     */
    public function count($search = ''): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuarios";
            $params = [];

            if (!empty($search)) {
                $sql .= " WHERE nome LIKE ? OR email LIKE ? OR login LIKE ?";
                $searchParam = "%{$search}%";
                $params = [$searchParam, $searchParam, $searchParam];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting users: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete user by ID
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if ($user) {
                unset($user['senha']); // Remove password from returned data
                return $user;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error finding user by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new user
     */
    public function create(array $data): ?int
    {
        try {
            $sql = "INSERT INTO usuarios (nome, login, email, senha, tipo, ativo) 
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                $data['nome'],
                $data['login'],
                $data['email'],
                password_hash($data['senha'], PASSWORD_DEFAULT),
                $data['tipo'] ?? 'operador',
                $data['ativo'] ?? 1
            ]);

            return $success ? $this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user
     */
    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $params = [];

            if (isset($data['nome'])) {
                $fields[] = 'nome = ?';
                $params[] = $data['nome'];
            }
            if (isset($data['login'])) {
                $fields[] = 'login = ?';
                $params[] = $data['login'];
            }
            if (isset($data['email'])) {
                $fields[] = 'email = ?';
                $params[] = $data['email'];
            }
            if (isset($data['senha'])) {
                $fields[] = 'senha = ?';
                $params[] = password_hash($data['senha'], PASSWORD_DEFAULT);
            }
            if (isset($data['tipo'])) {
                $fields[] = 'tipo = ?';
                $params[] = $data['tipo'];
            }
            if (isset($data['ativo'])) {
                $fields[] = 'ativo = ?';
                $params[] = $data['ativo'];
            }

            if (empty($fields)) {
                return false;
            }

            $params[] = $id;
            $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if login exists
     */
    public function loginExists(string $login, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE login = ?";
            $params = [$login];

            if ($excludeId !== null) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking login existence: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
            $params = [$email];

            if ($excludeId !== null) {
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
}
