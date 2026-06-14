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
     * Busca vendas de forma paginada aplicando os filtros enviados do Controller.
     * Atende à solicitação da banca de mover lógica de query para o Model.
     */
    public function listarVendasPaginadas(array $filtros)
    {
        $sql = "SELECT id, numero, nomeCliente, dataVenda, totalComDesconto, situacao FROM vendas WHERE 1=1";
        $params = [];

        if (!empty($filtros['cliente'])) {
            $sql .= " AND nomeCliente LIKE :cliente";
            $params['cliente'] = "%" . $filtros['cliente'] . "%";
        }

        if (!empty($filtros['dataInicio']) && !empty($filtros['dataFim'])) {
            $sql .= " AND dataVenda BETWEEN :inicio AND :fim";
            $params['inicio'] = $filtros['dataInicio'];
            $params['fim'] = $filtros['dataFim'];
        }

        $colunasPermitidas = ['dataVenda', 'totalComDesconto', 'nomeCliente'];
        $colunaOrdem = in_array($filtros['ordem'] ?? '', $colunasPermitidas) ? $filtros['ordem'] : 'dataVenda';

        $limite = 10;
        $offset = ((int)($filtros['pagina'] ?? 1) - 1) * $limite;
        $limiteParaCheck = $limite + 1;

        $sql .= " ORDER BY $colunaOrdem DESC, id DESC LIMIT $limiteParaCheck OFFSET $offset";

        $vendas = $this->query($sql, $params);

        $temMais = count($vendas) > $limite;
        if ($temMais) {
            array_pop($vendas);
        }

        return [
            'data' => $vendas,
            'temMais' => $temMais,
            'pagina' => $filtros['pagina'] ?? 1
        ];
    }

    /**
     * Retorna uma venda específica unificada com seus itens do banco de dados.
     */
    public function buscarPorIdComItens($id)
    {
        $venda = $this->query("SELECT * FROM vendas WHERE id = :id", ['id' => $id]);
        if (empty($venda)) {
            return null;
        }

        $itens = $this->query("SELECT * FROM vendas_itens WHERE venda_id = :id", ['id' => $id]);

        return [
            'venda' => $venda[0],
            'itens' => $itens
        ];
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
     * Otimizar Exclusão & Exclusão em Lote via Código (Sem ON DELETE CASCADE)
     * Remove os itens e as vendas controlando a consistência por transação.
     * * @param array $ids Vetor com os IDs das vendas a serem excluídas.
     * @return bool
     * @throws \Exception
     */
    public function excluirVendasEmLote(array $ids)
    {
        if (empty($ids)) {
            throw new \Exception("Nenhum ID fornecido para exclusão.");
        }

        // Cria uma string de placeholders (?, ?, ?) conforme a quantidade de IDs
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $this->db->beginTransaction();

        try {
            // 1. Remove os itens das vendas primeiro (Gestão de consistência via código)
            $sqlItens = "DELETE FROM vendas_itens WHERE venda_id IN ($placeholders)";
            $stmtItens = $this->db->prepare($sqlItens);
            $stmtItens->execute($ids);

            // 2. Remove as vendas em si
            $sqlVendas = "DELETE FROM vendas WHERE id IN ($placeholders)";
            $stmtVendas = $this->db->prepare($sqlVendas);
            $stmtVendas = $this->db->prepare($sqlVendas);
            $stmtVendas->execute($ids);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception("Erro ao processar exclusão em lote: " . $e->getMessage());
        }
    }
}
