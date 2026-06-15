CREATE TABLE IF NOT EXISTS tasks (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    status      VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

USE `devpool_erp`;

-- Cria a tabela nova de clientes separada
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(512) NOT NULL 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Modifica a tabela de vendas antiga 
-- Adiciona a nova coluna de vínculo (idCliente)
ALTER TABLE `vendas` 
ADD COLUMN `idCliente` INT NOT NULL AFTER `id`;

-- Cria a restrição de Chave Estrangeira (FK) para garantir a integridade
ALTER TABLE `vendas`
ADD CONSTRAINT `fk_vendas_cliente`
FOREIGN KEY (`idCliente`) REFERENCES `clientes`(`id`)
ON DELETE RESTRICT;

-- Remove o campo 'nomeCliente'
ALTER TABLE `vendas` 
DROP COLUMN `nomeCliente`;