<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model Venda: Centraliza as Regras de Negócio (RN) e persistência de dados.
 */
class Venda extends Model
{
    /**
     * Executa consultas preparadas (Prepared Statements) para evitar SQL Injection.
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * RN14 e RN19: Recalcula todos os valores no servidor.
     * Nunca confiamos apenas no cálculo feito pelo JavaScript/Frontend.
     * * @param array $dadosVenda Cabecalho da venda com o percentual de desconto.
     * @param array $itens Lista de itens da venda.
     * @return array Totais calculados.
     */
    private function calcularTotais(array $dadosVenda, array $itens)
    {
        $subtotal = 0;
        foreach ($itens as $item) {
            $preco = (float)($item['precoUnitario'] ?? 0);
            $quantidade = (int)($item['quantidade'] ?? 0);
            $subtotal += $quantidade * $preco;
        }

        $percentualDesconto = (float)($dadosVenda['percentualDesconto'] ?? 0);
        
        // Garante que o desconto esteja entre 0 e 100%
        $percentualDesconto = max(0, min(100, $percentualDesconto));
        
        $valorDesconto = $subtotal * ($percentualDesconto / 100);
        $totalComDesconto = $subtotal - $valorDesconto;

        return [
            'subtotal' => (float)$subtotal,
            'valorDesconto' => (float)$valorDesconto,
            'totalComDesconto' => (float)$totalComDesconto,
            'percentualDesconto' => (float)$percentualDesconto
        ];
    }

    /**
     * RN17: Salva a venda e seus itens usando transação atômica.
     */
    public function salvarVendaCompleta(array $dadosVenda, array $itens)
    {
        if (empty($itens)) {
            throw new \Exception("RN17: A venda deve conter pelo menos um item.");
        }

        $this->db->beginTransaction();

        try {
            $totais = $this->calcularTotais($dadosVenda, $itens);

            $sqlVenda = "INSERT INTO vendas (nomeCliente, dataVenda, percentualDesconto, subtotal, valorDesconto, totalComDesconto, situacao) 
                         VALUES (:nome, :data, :desconto, :subtotal, :v_desc, :total, 'Em aberto')";

            $stmtVenda = $this->db->prepare($sqlVenda);
            $stmtVenda->execute([
                ':nome'     => $dadosVenda['nomeCliente'],
                ':data'     => $dadosVenda['dataVenda'],
                ':desconto' => $totais['percentualDesconto'],
                ':subtotal' => $totais['subtotal'],
                ':v_desc'   => $totais['valorDesconto'],
                ':total'    => $totais['totalComDesconto']
            ]);

            $vendaId = $this->db->lastInsertId();

            // RN8: Preservar produto_id e nomeProduto original no momento da venda
            $sqlItem = "INSERT INTO vendas_itens (venda_id, produto_id, nomeProduto, quantidade, precoUnitario, totalItem) 
                        VALUES (:venda_id, :prod_id, :nome, :qtd, :preco, :total_item)";
            $stmtItem = $this->db->prepare($sqlItem);

            foreach ($itens as $item) {
                $qtd = (int)$item['quantidade'];
                $preco = (float)$item['precoUnitario'];
                
                $stmtItem->execute([
                    ':venda_id' => $vendaId,
                    ':prod_id'  => $item['produto_id'],
                    ':nome'     => $item['nomeProduto'],
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

    /**
     * Atualiza os dados de uma venda existente e sincroniza os itens.
     */
    public function atualizarVenda($id, array $dadosVenda, array $itens)
    {
        if (empty($itens)) {
            throw new \Exception("A venda não pode ficar sem itens.");
        }

        $this->db->beginTransaction();

        try {
            $totais = $this->calcularTotais($dadosVenda, $itens);

            $sql = "UPDATE vendas SET 
                        nomeCliente = :nome, 
                        dataVenda = :data,
                        percentualDesconto = :perc, 
                        subtotal = :sub, 
                        valorDesconto = :val_desc,
                        totalComDesconto = :total 
                    WHERE id = :id";

            $this->db->prepare($sql)->execute([
                ':nome'     => $dadosVenda['nomeCliente'],
                ':data'     => $dadosVenda['dataVenda'],
                ':perc'     => $totais['percentualDesconto'],
                ':sub'      => $totais['subtotal'],
                ':val_desc' => $totais['valorDesconto'],
                ':total'    => $totais['totalComDesconto'],
                ':id'       => $id
            ]);

            // UC16: Sincronização de itens (Delete & Insert)
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
                    ':nome'     => $item['nomeProduto'],
                    ':qtd'      => $qtd,
                    ':preco'    => $preco,
                    ':total_item' => $qtd * $preco
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Exclui uma venda. 
     * Nota: No banco de dados, certifique-se que a FK de itens está com ON DELETE CASCADE.
     */
    public function excluirVendaCompleta($id)
    {
        $sql = "DELETE FROM vendas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}