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

            // Create contract
            $stmt = $this->db->prepare("
                INSERT INTO contratacoes (cliente_id, total, status) 
                VALUES (?, ?, 'confirmada')
            ");
            $stmt->execute([$clienteId, $total]);
            $contratacaoId = $this->db->lastInsertId();

            // Add services to contract
            $stmt = $this->db->prepare("
                INSERT INTO contratacao_servicos (contratacao_id, servico_id, data_id, preco) 
                VALUES (?, ?, ?, ?)
            ");

            foreach ($cartItems as $item) {
                // Check if date is still available
                $checkStmt = $this->db->prepare("
                    SELECT disponivel FROM datas_disponiveis 
                    WHERE id = ? FOR UPDATE
                ");
                $checkStmt->execute([$item['data_id']]);
                $dateAvailable = $checkStmt->fetchColumn();

                if (!$dateAvailable || $dateAvailable != 1) {
                    throw new Exception("Date is no longer available");
                }

                // Insert contract service
                $stmt->execute([
                    $contratacaoId,
                    $item['servico_id'],
                    $item['data_id'],
                    $item['preco']
                ]);

                // Mark date as unavailable
                $updateStmt = $this->db->prepare("
                    UPDATE datas_disponiveis 
                    SET disponivel = 0 
                    WHERE id = ?
                ");
                $updateStmt->execute([$item['data_id']]);
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
                ORDER BY c.created_at DESC
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
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                AND YEAR(created_at) = YEAR(CURRENT_DATE())
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
                ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

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

    public function listarServicosPorContratacao($contratacaoId)
    {
        $sql = "SELECT cs.*, s.nome as servico_nome, s.tipo, dd.data
                FROM contratacao_servicos cs
                INNER JOIN servicos s ON cs.servico_id = s.id
                INNER JOIN datas_disponiveis dd ON cs.data_disponivel_id = dd.id
                WHERE cs.contratacao_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contratacaoId]);

        return $stmt->fetchAll();
    }

    public function buscarPorCliente($clienteId)
    {
        $sql = "SELECT * FROM contratacoes WHERE cliente_id = ? ORDER BY created_at DESC";
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

                require_once 'dao/DataDisponivelDAO.php';
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
}
