-- ============================================
-- Script de inicialização do banco de dados
-- Este arquivo é executado automaticamente
-- quando o container MySQL é criado pela primeira vez
-- ============================================

CREATE SCHEMA IF NOT EXISTS `devpool_erp` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `devpool_erp`;

-- Tabela de exemplo para testes
CREATE TABLE IF NOT EXISTS `devpool_erp`.`exemplo` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nome` VARCHAR(255) NOT NULL,
	`dataCriacao` DATETIME NULL DEFAULT CURRENT_TIMESTAMP(),
	PRIMARY KEY (`id`)
);

-- ============================================
-- Dados iniciais para testes
-- Estes registros permitem testar a API imediatamente
-- ============================================

INSERT INTO `exemplo` (`nome`) VALUES 
    ('Primeiro registro de exemplo'),
    ('Segundo registro de exemplo'),
    ('Terceiro registro de exemplo'),
    ('Quarto registro de exemplo'),
    ('Quinto registro de exemplo');

-- ============================================
-- ADICIONE SUAS TABELAS E DADOS ABAIXO
-- ============================================

-- Tabela principal de Vendas (R5, R11)
CREATE TABLE IF NOT EXISTS `devpool_erp`.`vendas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `numero` VARCHAR(20) NULL, -- Tornar null caso use o ID como número
  `nomeCliente` VARCHAR(255) NOT NULL,
  `dataVenda` DATE NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `valorDesconto` DECIMAL(10,2) DEFAULT 0.00, -- Adicionado para facilitar cálculos (RN12)
  `percentualDesconto` DECIMAL(5,2) DEFAULT 0.00,
  `totalComDesconto` DECIMAL(10,2) NOT NULL,
  `situacao` VARCHAR(50) DEFAULT 'Em aberto',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Itens da Venda (R8, RN18)
-- Relaciona os produtos do Bling com a venda local
CREATE TABLE IF NOT EXISTS `devpool_erp`.`vendas_itens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `venda_id` INT NOT NULL,
  `produto_id` BIGINT NOT NULL, 
  `nomeProduto` VARCHAR(255) NOT NULL,
  `quantidade` INT NOT NULL,
  `precoUnitario` DECIMAL(10,2) NOT NULL, -- Preço na hora da venda (RN6)
  `totalItem` DECIMAL(10,2) NOT NULL,
  CONSTRAINT `fk_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir algumas vendas de teste
INSERT INTO `devpool_erp`.`vendas` (`numero`, `nomeCliente`, `dataVenda`, `subtotal`, `percentualDesconto`, `totalComDesconto`, `situacao`) 
VALUES 
('105', 'Maria Silva', '2026-04-15', 250.00, 0.00, 250.00, 'Finalizada');

-- Inserir itens para a venda 105 (exemplo de relacionamento)
-- Nota: O venda_id deve corresponder ao ID gerado na tabela vendas
INSERT INTO `devpool_erp`.`vendas_itens` (`venda_id`, `produto_id`, `nomeProduto`, `quantidade`, `precoUnitario`, `totalItem`)
VALUES 
(1, 12345678, 'Produto Exemplo A', 2, 100.00, 200.00);