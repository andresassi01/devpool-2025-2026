<?php

namespace App\Models;

use App\Core\Model;

class Venda extends Model
{
    public function query($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function salvarVendaCompleta($dadosVenda, $itens)
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO vendas (numero, nomeCliente, dataVenda, subtotal, valorDesconto, percentualDesconto, totalComDesconto, situacao) 
                    VALUES (:numero, :nome, :data, :sub, :val_desc, :perc_desc, :total, :situ)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':numero'   => $dadosVenda['numero'] ?? null,
                ':nome'     => $dadosVenda['nomeCliente'],
                ':data'     => $dadosVenda['dataVenda'],
                ':sub'      => $dadosVenda['subtotal'],
                ':val_desc' => $dadosVenda['valorDesconto'] ?? 0,
                ':perc_desc'=> $dadosVenda['percentualDesconto'] ?? 0,
                ':total'    => $dadosVenda['totalComDesconto'],
                ':situ'     => $dadosVenda['situacao'] ?? 'Em aberto'
            ]);

            $vendaId = $this->db->lastInsertId();

            if (empty($dadosVenda['numero'])) {
                $this->db->prepare("UPDATE vendas SET numero = :id WHERE id = :id")
                         ->execute([':id' => $vendaId]);
            }

            $sqlItem = "INSERT INTO vendas_itens (venda_id, produto_id, nomeProduto, quantidade, precoUnitario, totalItem) 
                        VALUES (:venda_id, :prod_id, :nome, :qtd, :preco, :total)";

            $stmtItem = $this->db->prepare($sqlItem);

            foreach ($itens as $item) {
                $stmtItem->execute([
                    ':venda_id' => $vendaId,
                    ':prod_id'  => $item['produto_id'] ?? $item['id'], // Aceita ambos os formatos
                    ':nome'     => $item['nomeProduto'] ?? $item['nome'],
                    ':qtd'      => $item['quantidade'],
                    ':preco'    => $item['precoUnitario'] ?? $item['preco'],
                    ':total'    => $item['quantidade'] * ($item['precoUnitario'] ?? $item['preco'])
                ]);
            }

            $this->db->commit();
            return $vendaId;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}