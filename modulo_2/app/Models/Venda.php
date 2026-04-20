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

    /**
     * RN14 e RN19: Recalcula os valores no servidor para garantir a integridade.
     */
    private function calcularTotais($dadosVenda, $itens)
    {
        $subtotal = 0;
        foreach ($itens as $item) {
            $preco = (float)($item['precoUnitario'] ?? 0);
            $quantidade = (int)($item['quantidade'] ?? 0);
            $subtotal += $quantidade * $preco;
        }

        $percentualDesconto = (float)($dadosVenda['percentualDesconto'] ?? 0);
        $valorDesconto = $subtotal * ($percentualDesconto / 100);
        $totalComDesconto = $subtotal - $valorDesconto;

        return [
            'subtotal' => $subtotal,
            'valorDesconto' => $valorDesconto,
            'totalComDesconto' => $totalComDesconto
        ];
    }

    public function salvarVendaCompleta(array $dadosVenda, array $itens)
    {
        // RN17 - Trava de segurança
        if (empty($itens)) {
            throw new \Exception("A venda deve conter pelo menos um item.");
        }

        $this->db->beginTransaction();

        try {
            $totais = $this->calcularTotais($dadosVenda, $itens);

            $sqlVenda = "INSERT INTO vendas (nomeCliente, dataVenda, percentualDesconto, subtotal, valorDesconto, totalComDesconto) 
                         VALUES (:nome, :data, :desconto, :subtotal, :v_desc, :total)";

            $stmtVenda = $this->db->prepare($sqlVenda);
            $stmtVenda->execute([
                ':nome'     => $dadosVenda['nomeCliente'],
                ':data'     => $dadosVenda['dataVenda'],
                ':desconto' => $dadosVenda['percentualDesconto'],
                ':subtotal' => $totais['subtotal'],
                ':v_desc'   => $totais['valorDesconto'],
                ':total'    => $totais['totalComDesconto']
            ]);

            $vendaId = $this->db->lastInsertId();

            // RN8: Preservar produto_id e nomeProduto
            $sqlItem = "INSERT INTO vendas_itens (venda_id, produto_id, nomeProduto, quantidade, precoUnitario, totalItem) 
                        VALUES (:venda_id, :prod_id, :nome, :qtd, :preco, :total_item)";
            $stmtItem = $this->db->prepare($sqlItem);

            foreach ($itens as $item) {
                $qtd = (int)$item['quantidade'];
                $preco = (float)$item['precoUnitario'];
                
                $stmtItem->execute([
                    ':venda_id' => $vendaId,
                    ':prod_id'  => $item['produto_id'],
                    ':nome'     => $item['nomeProduto'], // RN8 garantida
                    ':qtd'      => $qtd,
                    ':preco'    => $preco,
                    ':total_item' => $qtd * $preco
                ]);
            }

            $this->db->commit();
            return $vendaId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function atualizarVenda($id, $dadosVenda, $itens)
    {
        if (empty($itens)) {
            throw new \Exception("A venda não pode ficar sem itens.");
        }

        try {
            $this->db->beginTransaction();

            $totais = $this->calcularTotais($dadosVenda, $itens);

            $sql = "UPDATE vendas SET 
                nomeCliente = :nome, 
                percentualDesconto = :perc, 
                subtotal = :sub, 
                valorDesconto = :val_desc,
                totalComDesconto = :total 
                WHERE id = :id";

            $this->db->prepare($sql)->execute([
                ':nome'     => $dadosVenda['nomeCliente'],
                ':perc'     => $dadosVenda['percentualDesconto'],
                ':sub'      => $totais['subtotal'],
                ':val_desc' => $totais['valorDesconto'],
                ':total'    => $totais['totalComDesconto'],
                ':id'        => $id
            ]);

            // UC16: Remove e insere novament
            $this->db->prepare("DELETE FROM vendas_itens WHERE venda_id = :id")->execute([':id' => $id]);

            $sqlItem = "INSERT INTO vendas_itens (venda_id, produto_id, nomeProduto, quantidade, precoUnitario, totalItem) 
                        VALUES (:venda_id, :prod_id, :nome, :qtd, :preco, :total_item)";
            $stmtItem = $this->db->prepare($sqlItem);

            foreach ($itens as $item) {
                $qtd = (int)$item['quantidade'];
                $preco = (float)$item['precoUnitario'];

                $stmtItem->execute([
                    ':venda_id' => $id,
                    ':prod_id'  => $item['produto_id'],
                    ':nome'     => $item['nomeProduto'], // RN8 garantida
                    ':qtd'      => $qtd,
                    ':preco'    => $preco,
                    ':total_item' => $qtd * $preco
                ]);
            }

            $this->db->commit();
            return true;
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
            // Se houver FK com CASCADE no banco, isso apaga os itens automaticamente.
            $sql = "DELETE FROM vendas WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}