<?php

require_once __DIR__ . '/../config/db.php';

class ClienteDAO
{
    private $conexao;

    public function __construct()
    {
        $this->conexao = (new Conexao())->getConexao();
    }

    public function autenticar($email, $senha)
    {
        $sql = "SELECT * FROM clientes WHERE email = ? AND senha = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindParam(1, strtolower($email));
        $stmt->bindParam(2, $senha);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $cliente = $stmt->fetch(PDO::FETCH_OBJ);

            return $cliente;
        }

        return null;
    }
}
