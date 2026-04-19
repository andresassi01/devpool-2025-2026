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
                ':perc_desc' => $dadosVenda['percentualDesconto'] ?? 0,
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
    public function excluirVendaCompleta($id)
    {
        try {
            // Com o ON DELETE CASCADE no SQL, 
            // deletar a venda já remove os itens automaticamente!
            $sql = "DELETE FROM vendas WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function atualizarVenda($id, $dadosVenda, $itens)
    {
        try {
            $this->db->beginTransaction();

            // Atualiza a venda principal
            $sql = "UPDATE vendas SET 
                nomeCliente = :nome, 
                dataVenda = :data,
                percentualDesconto = :perc, 
                subtotal = :sub, 
                totalComDesconto = :total 
                WHERE id = :id";
            $this->db->prepare($sql)->execute([
                ':nome' => $dadosVenda['nomeCliente'],
                ':data' => $dadosVenda['dataVenda'],
                ':perc' => $dadosVenda['percentualDesconto'],
                ':sub'  => $dadosVenda['subtotal'],
                ':total' => $dadosVenda['totalComDesconto'],
                ':id'   => $id
            ]);

            // Remove os itens antigos e insere os novos (mais simples que fazer UPDATE em cada item)
            $this->db->prepare("DELETE FROM vendas_itens WHERE venda_id = :id")->execute([':id' => $id]);

            $sqlItem = "INSERT INTO vendas_itens (venda_id, produto_id, nomeProduto, quantidade, precoUnitario, totalItem) 
                    VALUES (:venda_id, :prod_id, :nome, :qtd, :preco, :total)";
            $stmtItem = $this->db->prepare($sqlItem);
            foreach ($itens as $item) {
                $stmtItem->execute([
                    ':venda_id' => $id,
                    ':prod_id'  => $item['produto_id'],
                    ':nome'     => $item['nomeProduto'],
                    ':qtd'      => $item['quantidade'],
                    ':preco'    => $item['precoUnitario'],
                    ':total'    => $item['quantidade'] * $item['precoUnitario']
                ]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
