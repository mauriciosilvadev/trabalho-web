<?php

require_once __DIR__ . '/../config/db.php';

/**
 * Data Access Object for services
 */
class ServicoDAO
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all services
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, COUNT(dd.id) as total_datas, 
                       SUM(CASE WHEN dd.disponivel = 1 THEN 1 ELSE 0 END) as datas_disponiveis
                FROM servicos s
                LEFT JOIN datas_disponiveis dd ON s.id = dd.servico_id
                GROUP BY s.id
                ORDER BY s.nome
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching all services: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find service by ID
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM servicos WHERE id = ?");
            $stmt->execute([$id]);
            $service = $stmt->fetch();
            return $service ?: null;
        } catch (PDOException $e) {
            error_log("Error finding service by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new service
     */
    public function create(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO servicos (nome, tipo, preco, descricao) 
                VALUES (?, ?, ?, ?)
            ");

            $result = $stmt->execute([
                $data['nome'],
                $data['tipo'],
                $data['preco'],
                $data['descricao']
            ]);

            return $result ? $this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("Error creating service: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update service
     */
    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE servicos 
                SET nome = ?, tipo = ?, preco = ?, descricao = ?
                WHERE id = ?
            ");

            return $stmt->execute([
                $data['nome'],
                $data['tipo'],
                $data['preco'],
                $data['descricao'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating service: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete service
     */
    public function delete(int $id): bool
    {
        try {
            // Check if service has contracts
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM contratacao_servicos WHERE servico_id = ?");
            $stmt->execute([$id]);

            if ($stmt->fetchColumn() > 0) {
                error_log("Cannot delete service with existing contracts");
                return false;
            }

            // Delete available dates first
            $stmt = $this->db->prepare("DELETE FROM datas_disponiveis WHERE servico_id = ?");
            $stmt->execute([$id]);

            // Delete service
            $stmt = $this->db->prepare("DELETE FROM servicos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting service: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search services by filters
     */
    public function search(array $filters): array
    {
        try {
            $sql = "
                SELECT DISTINCT s.*, COUNT(dd.id) as total_datas, 
                       SUM(CASE WHEN dd.disponivel = 1 THEN 1 ELSE 0 END) as datas_disponiveis
                FROM servicos s
                LEFT JOIN datas_disponiveis dd ON s.id = dd.servico_id
                WHERE 1=1
            ";
            $params = [];

            if (!empty($filters['id'])) {
                $sql .= " AND s.id = ?";
                $params[] = $filters['id'];
            }

            if (!empty($filters['nome'])) {
                $sql .= " AND s.nome LIKE ?";
                $params[] = '%' . $filters['nome'] . '%';
            }

            if (!empty($filters['tipo'])) {
                $sql .= " AND s.tipo LIKE ?";
                $params[] = '%' . $filters['tipo'] . '%';
            }

            if (!empty($filters['preco_min'])) {
                $sql .= " AND s.preco >= ?";
                $params[] = $filters['preco_min'];
            }

            if (!empty($filters['preco_max'])) {
                $sql .= " AND s.preco <= ?";
                $params[] = $filters['preco_max'];
            }

            if (!empty($filters['data_disponivel'])) {
                $sql .= " AND dd.data = ? AND dd.disponivel = 1";
                $params[] = $filters['data_disponivel'];
            }

            $sql .= " GROUP BY s.id ORDER BY s.nome";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error searching services: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get services with available dates
     */
    public function findWithAvailableDates(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT s.*
                FROM servicos s
                INNER JOIN datas_disponiveis dd ON s.id = dd.servico_id
                WHERE dd.disponivel = 1 AND dd.data >= CURDATE()
                ORDER BY s.nome
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching services with available dates: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get service types
     */
    public function getTypes(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT DISTINCT tipo FROM servicos ORDER BY tipo");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error fetching service types: " . $e->getMessage());
            return [];
        }
    }

    public function listarTodos()
    {
        $sql = "SELECT * FROM servicos WHERE ativo = 1 ORDER BY nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM servicos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function inserir($nome, $tipo, $preco, $descricao)
    {
        $this->db->beginTransaction();

        try {
            $sql = "INSERT INTO servicos (nome, tipo, preco, descricao) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nome, $tipo, $preco, $descricao]);

            $servicoId = $this->db->lastInsertId();

            $this->db->commit();
            return $servicoId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function atualizar($id, $nome, $tipo, $preco, $descricao)
    {
        $sql = "UPDATE servicos SET nome = ?, tipo = ?, preco = ?, descricao = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$nome, $tipo, $preco, $descricao, $id]);
    }

    public function excluir($id)
    {
        $sql = "UPDATE servicos SET ativo = 0 WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }

    public function buscar($termo, $tipo = null, $precoMin = null, $precoMax = null, $data = null)
    {
        $sql = "SELECT DISTINCT s.* FROM servicos s";
        $params = [];
        $conditions = ["s.ativo = 1"];

        if ($termo) {
            $conditions[] = "(s.nome LIKE ? OR s.tipo LIKE ? OR s.descricao LIKE ?)";
            $termoLike = "%{$termo}%";
            $params[] = $termoLike;
            $params[] = $termoLike;
            $params[] = $termoLike;
        }

        if ($tipo) {
            $conditions[] = "s.tipo = ?";
            $params[] = $tipo;
        }

        if ($precoMin !== null) {
            $conditions[] = "s.preco >= ?";
            $params[] = $precoMin;
        }

        if ($precoMax !== null) {
            $conditions[] = "s.preco <= ?";
            $params[] = $precoMax;
        }

        if ($data) {
            $sql .= " INNER JOIN datas_disponiveis dd ON s.id = dd.servico_id";
            $conditions[] = "dd.data = ? AND dd.disponivel = 1";
            $params[] = $data;
        }

        $sql .= " WHERE " . implode(" AND ", $conditions);
        $sql .= " ORDER BY s.nome";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function listarTipos()
    {
        $sql = "SELECT DISTINCT tipo FROM servicos WHERE ativo = 1 ORDER BY tipo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function temDatasDisponiveis($id)
    {
        $sql = "SELECT COUNT(*) FROM datas_disponiveis WHERE servico_id = ? AND disponivel = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetchColumn() > 0;
    }

    public function temContratacoes($id)
    {
        $sql = "SELECT COUNT(*) FROM contratacao_servicos WHERE servico_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetchColumn() > 0;
    }
}
