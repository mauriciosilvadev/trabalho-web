<?php

require_once __DIR__ . '/../config/db.php';

/**
 * Data Access Object for contracts
 */
class ContratacaoDAO
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new contract with services
     */
    public function create(int $clienteId, array $cartItems, float $total): ?int
    {
        try {
            $this->db->beginTransaction();

            // Create contract with valor_total field
            $stmt = $this->db->prepare("
                INSERT INTO contratacoes (cliente_id, total, valor_total, status) 
                VALUES (?, ?, ?, 'confirmada')
            ");
            $stmt->execute([$clienteId, $total, $total]);
            $contratacaoId = $this->db->lastInsertId();

            // Add services to contract
            $stmt = $this->db->prepare("
                INSERT INTO contratacao_servicos (contrato_id, contratacao_id, servico_id, data_id, preco, valor, quantidade) 
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");

            foreach ($cartItems as $item) {
                // Get the correct service ID
                $servicoId = isset($item['id']) ? $item['id'] : (isset($item['servico_id']) ? $item['servico_id'] : null);
                $dataId = isset($item['data_id']) ? $item['data_id'] : null;
                $preco = isset($item['preco']) ? $item['preco'] : 0;

                if (!$servicoId || !$dataId) {
                    throw new Exception("Dados do serviço incompletos");
                }

                // Check if date is still available
                $checkStmt = $this->db->prepare("
                    SELECT disponivel FROM datas_disponiveis 
                    WHERE id = ? FOR UPDATE
                ");
                $checkStmt->execute([$dataId]);
                $dateAvailable = $checkStmt->fetchColumn();

                if (!$dateAvailable || $dateAvailable != 1) {
                    throw new Exception("Data não está mais disponível");
                }

                // Insert contract service
                $stmt->execute([
                    $contratacaoId,  // contrato_id
                    $contratacaoId,  // contratacao_id (mesma referência)
                    $servicoId,
                    $dataId,
                    $preco,
                    $preco
                ]);

                // Mark date as unavailable
                $updateStmt = $this->db->prepare("
                    UPDATE datas_disponiveis 
                    SET disponivel = 0 
                    WHERE id = ?
                ");
                $updateStmt->execute([$dataId]);
            }

            $this->db->commit();
            return $contratacaoId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error creating contract: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new contract (simplified version)
     */
    public function createContract(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO contratacoes (cliente_id, usuario_id, valor_total, status, observacoes) 
                VALUES (?, ?, ?, ?, ?)
            ");

            $success = $stmt->execute([
                $data['cliente_id'],
                $data['usuario_id'],
                $data['valor_total'],
                $data['status'] ?? 'ativo',
                $data['observacoes'] ?? null
            ]);

            return $success ? $this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("Error creating contract: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->db->rollBack();
    }

    /**
     * Add service to contract
     */
    public function addService(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO contratacao_servicos (contrato_id, servico_id, quantidade, preco_unitario, subtotal) 
                VALUES (?, ?, ?, ?, ?)
            ");

            $success = $stmt->execute([
                $data['contrato_id'],
                $data['servico_id'],
                $data['quantidade'],
                $data['preco_unitario'],
                $data['subtotal']
            ]);

            return $success ? $this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("Error adding service to contract: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add scheduled date
     */
    public function addScheduledDate(array $data): ?int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO agendamentos (contrato_servico_id, data_agendada, status) 
                VALUES (?, ?, ?)
            ");

            $success = $stmt->execute([
                $data['contrato_servico_id'],
                $data['data_agendada'],
                $data['status'] ?? 'agendado'
            ]);

            return $success ? $this->db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("Error adding scheduled date: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all contracts
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email
                FROM contratacoes c
                INNER JOIN clientes cl ON c.cliente_id = cl.id
                ORDER BY c.criado_em DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching all contracts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get contracts statistics
     */
    public function getStatistics(): array
    {
        try {
            $stats = [];

            // Total contracts
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM contratacoes");
            $stmt->execute();
            $stats['total_contratos'] = $stmt->fetchColumn();

            // Total revenue
            $stmt = $this->db->prepare("SELECT SUM(total) as revenue FROM contratacoes WHERE status = 'confirmada'");
            $stmt->execute();
            $stats['receita_total'] = $stmt->fetchColumn() ?: 0;

            // Contracts this month
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM contratacoes 
                WHERE MONTH(criado_em) = MONTH(CURRENT_DATE()) 
                AND YEAR(criado_em) = YEAR(CURRENT_DATE())
            ");
            $stmt->execute();
            $stats['contratos_mes'] = $stmt->fetchColumn();

            // Most contracted services
            $stmt = $this->db->prepare("
                SELECT s.nome, COUNT(*) as total
                FROM contratacao_servicos cs
                INNER JOIN servicos s ON cs.servico_id = s.id
                GROUP BY s.id, s.nome
                ORDER BY total DESC
                LIMIT 5
            ");
            $stmt->execute();
            $stats['servicos_populares'] = $stmt->fetchAll();

            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return [];
        }
    }

    public function listarTodas()
    {
        $sql = "SELECT c.*, cl.nome as cliente_nome, cl.cpf, cl.cidade 
                FROM contratacoes c 
                INNER JOIN clientes cl ON c.cliente_id = cl.id 
                ORDER BY c.criado_em DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getAll($filters = [])
    {
        $sql = "SELECT 
            c.id,
            c.cliente_id,
            c.status,
            c.total as valor_total,
            c.criado_em,
            cl.nome as cliente_nome,
            cl.email as cliente_email,
            COUNT(cs.id) as total_servicos
        FROM contratacoes c 
        INNER JOIN clientes cl ON c.cliente_id = cl.id 
        LEFT JOIN contratacao_servicos cs ON c.id = cs.contratacao_id
        WHERE 1=1";

        $params = [];

        // Apply filters
        if (!empty($filters['cliente_id'])) {
            $sql .= " AND c.cliente_id = ?";
            $params[] = $filters['cliente_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['data_inicio'])) {
            $sql .= " AND DATE(c.criado_em) >= ?";
            $params[] = $filters['data_inicio'];
        }

        if (!empty($filters['data_fim'])) {
            $sql .= " AND DATE(c.criado_em) <= ?";
            $params[] = $filters['data_fim'];
        }

        if (!empty($filters['servico_nome'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM contratacao_servicos cs2 
                INNER JOIN servicos s ON cs2.servico_id = s.id 
                WHERE cs2.contratacao_id = c.id AND s.nome LIKE ?
            )";
            $params[] = '%' . $filters['servico_nome'] . '%';
        }

        $sql .= " GROUP BY c.id, c.cliente_id, c.status, c.total, c.criado_em, cl.nome, cl.email";
        $sql .= " ORDER BY c.criado_em DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT c.*, cl.nome as cliente_nome, cl.cpf, cl.cidade, cl.email, cl.telefone
                FROM contratacoes c 
                INNER JOIN clientes cl ON c.cliente_id = cl.id 
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function inserir($clienteId, $valorTotal)
    {
        $sql = "INSERT INTO contratacoes (cliente_id, valor_total, status) VALUES (?, ?, 'pendente')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId, $valorTotal]);

        return $this->db->lastInsertId();
    }

    public function inserirServico($contratacaoId, $servicoId, $dataDisponivelId, $valor)
    {
        $sql = "INSERT INTO contratacao_servicos (contratacao_id, servico_id, data_disponivel_id, valor) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$contratacaoId, $servicoId, $dataDisponivelId, $valor]);
    }

    public function confirmar($id)
    {
        $sql = "UPDATE contratacoes SET status = 'confirmada' WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }

    public function cancelar($id)
    {
        $sql = "UPDATE contratacoes SET status = 'cancelada' WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }

    public function updateStatus($id, $status)
    {
        $allowedStatuses = ['pendente', 'ativo', 'confirmada', 'concluido', 'cancelado', 'cancelada'];
        if (!in_array($status, $allowedStatuses)) {
            throw new Exception('Status inválido: ' . $status);
        }

        $sql = "UPDATE contratacoes SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    public function listarServicosPorContratacao($contratacaoId)
    {
        $sql = "SELECT cs.*, s.nome as servico_nome, s.tipo, dd.data
                FROM contratacao_servicos cs
                INNER JOIN servicos s ON cs.servico_id = s.id
                LEFT JOIN datas_disponiveis dd ON cs.data_id = dd.id
                WHERE cs.contratacao_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contratacaoId]);

        return $stmt->fetchAll();
    }

    public function buscarPorCliente($clienteId)
    {
        $sql = "SELECT * FROM contratacoes WHERE cliente_id = ? ORDER BY criado_em DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId]);

        return $stmt->fetchAll();
    }

    public function processarContratacao($clienteId, $servicos)
    {
        $this->db->beginTransaction();

        try {
            $valorTotal = 0;
            foreach ($servicos as $servico) {
                $valorTotal += $servico['valor'];
            }

            $contratacaoId = $this->inserir($clienteId, $valorTotal);

            foreach ($servicos as $servico) {
                $this->inserirServico(
                    $contratacaoId,
                    $servico['servico_id'],
                    $servico['data_disponivel_id'],
                    $servico['valor']
                );

                require_once __DIR__ . '/DataDisponivelDAO.php';
                $dataDAO = new DataDisponivelDAO();
                $dataDAO->marcarComoIndisponivel($servico['data_disponivel_id']);
            }

            $this->db->commit();
            return $contratacaoId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Get monthly revenue for current month
     */
    public function getMonthlyRevenue(): float
    {
        try {
            $sql = "SELECT COALESCE(SUM(valor_total), 0) as receita_mensal 
                    FROM contratacoes 
                    WHERE status = 'confirmada' 
                    AND MONTH(criado_em) = MONTH(CURRENT_DATE()) 
                    AND YEAR(criado_em) = YEAR(CURRENT_DATE())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return (float) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error getting monthly revenue: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get yearly revenue for current year
     */
    public function getYearlyRevenue(): float
    {
        try {
            $sql = "SELECT COALESCE(SUM(valor_total), 0) as receita_anual 
                    FROM contratacoes 
                    WHERE status = 'confirmada' 
                    AND YEAR(criado_em) = YEAR(CURRENT_DATE())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return (float) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error getting yearly revenue: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Get contracts by month for current year
     */
    public function getContractsByMonth(): array
    {
        try {
            $sql = "SELECT 
                        MONTH(criado_em) as mes,
                        COUNT(*) as total_contratos,
                        COALESCE(SUM(valor_total), 0) as receita_mensal
                    FROM contratacoes 
                    WHERE status = 'confirmada' 
                    AND YEAR(criado_em) = YEAR(CURRENT_DATE())
                    GROUP BY MONTH(criado_em)
                    ORDER BY mes";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting contracts by month: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get detailed financial report
     */
    public function getFinancialReport($startDate = null, $endDate = null): array
    {
        try {
            $whereClause = "WHERE status = 'confirmada'";
            $params = [];
            
            if ($startDate && $endDate) {
                $whereClause .= " AND criado_em BETWEEN ? AND ?";
                $params = [$startDate, $endDate];
            }
            
            $sql = "SELECT 
                        DATE(criado_em) as data,
                        COUNT(*) as total_contratos,
                        COALESCE(SUM(valor_total), 0) as receita_diaria,
                        COALESCE(AVG(valor_total), 0) as ticket_medio
                    FROM contratacoes 
                    $whereClause
                    GROUP BY DATE(criado_em)
                    ORDER BY data DESC
                    LIMIT 30";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting financial report: " . $e->getMessage());
            return [];
        }
    }
}
