<?php

require_once __DIR__ . '/../config/db.php';

/**
 * Data Access Object for available dates
 */
class DataDisponivelDAO
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get available dates for a service
     */
    public function findByServiceId(int $serviceId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM datas_disponiveis 
                WHERE servico_id = ? AND data >= CURDATE()
                ORDER BY data
            ");
            $stmt->execute([$serviceId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching dates for service: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available dates for a service (only available ones)
     */
    public function findAvailableByServiceId(int $serviceId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM datas_disponiveis 
                WHERE servico_id = ? AND disponivel = 1 AND data >= CURDATE()
                ORDER BY data
            ");
            $stmt->execute([$serviceId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching available dates for service: " . $e->getMessage());
            return [];
        }
    }

    public function listarPorServico($servicoId)
    {
        $sql = "SELECT * FROM datas_disponiveis WHERE servico_id = ? AND disponivel = 1 ORDER BY data";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$servicoId]);

        return $stmt->fetchAll();
    }

    public function inserirDatas($servicoId, $datas)
    {
        $this->db->beginTransaction();

        try {
            $sql = "INSERT INTO datas_disponiveis (servico_id, data, disponivel) VALUES (?, ?, 1)";
            $stmt = $this->db->prepare($sql);

            foreach ($datas as $data) {
                $stmt->execute([$servicoId, $data]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function marcarComoIndisponivel($id)
    {
        $sql = "UPDATE datas_disponiveis SET disponivel = 0 WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }

    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM datas_disponiveis WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function contarDatasPorServico($servicoId)
    {
        $sql = "SELECT COUNT(*) FROM datas_disponiveis WHERE servico_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$servicoId]);

        return $stmt->fetchColumn();
    }

    public function verificarDisponibilidade($servicoId, $data)
    {
        $sql = "SELECT id, disponivel FROM datas_disponiveis WHERE servico_id = ? AND data = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$servicoId, $data]);

        return $stmt->fetch();
    }

    public function excluirPorServico($servicoId)
    {
        $sql = "DELETE FROM datas_disponiveis WHERE servico_id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$servicoId]);
    }
}
