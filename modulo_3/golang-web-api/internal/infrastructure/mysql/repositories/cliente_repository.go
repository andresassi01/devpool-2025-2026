package repositories

import (
	"context"
	"database/sql"
	"errors"
	"fmt"

	"github.com/bling-lwsa/devpool-base-web-api/internal/domain/entities"
)

// ClienteRepositoryMysql implementa a interface interfaces.ClienteRepository usando MySQL.
type ClienteRepositoryMysql struct {
	db *sql.DB
}

// NewClienteRepositoryMysql cria uma nova instância do repositório de clientes.
func NewClienteRepositoryMysql(db *sql.DB) *ClienteRepositoryMysql {
	return &ClienteRepositoryMysql{db: db}
}

// Create insere um novo cliente no banco de dados.
func (r *ClienteRepositoryMysql) Create(ctx context.Context, cliente *entities.Cliente) error {
	query := "INSERT INTO clientes (nome) VALUES (?)"

	result, err := r.db.ExecContext(ctx, query, cliente.Nome)
	if err != nil {
		return fmt.Errorf("erro ao inserir cliente: %w", err)
	}

	id, err := result.LastInsertId()
	if err != nil {
		return fmt.Errorf("erro ao obter ID gerado: %w", err)
	}

	cliente.ID = id
	return nil
}

// GetByID busca um cliente específico pelo seu ID.
func (r *ClienteRepositoryMysql) GetByID(ctx context.Context, id int64) (*entities.Cliente, error) {
	query := "SELECT id, nome FROM clientes WHERE id = ?"

	var cliente entities.Cliente
	err := r.db.QueryRowContext(ctx, query, id).Scan(&cliente.ID, &cliente.Nome)

	if err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return nil, nil // Retorna nil se o cliente não for encontrado
		}
		return nil, fmt.Errorf("erro ao buscar cliente por ID: %w", err)
	}

	return &cliente, nil
}

// List retorna uma lista paginada de clientes com suporte a filtro por nome (LIKE).
func (r *ClienteRepositoryMysql) List(ctx context.Context, filtroNome string, limit, offset int) ([]entities.Cliente, error) {
	// Query base utilizando o LIKE exigido pelo diagrama do PDF para busca descritiva
	query := "SELECT id, nome FROM clientes WHERE nome LIKE ? LIMIT ? OFFSET ?"

	// Prepara o termo de busca para o SQL (ex: %andre%)
	termo := "%" + filtroNome + "%"

	rows, err := r.db.QueryContext(ctx, query, termo, limit, offset)
	if err != nil {
		return nil, fmt.Errorf("erro ao listar clientes: %w", err)
	}
	defer rows.Close()

	var clientes []entities.Cliente
	for rows.Next() {
		var c entities.Cliente
		if err := rows.Scan(&c.ID, &c.Nome); err != nil {
			return nil, fmt.Errorf("erro ao mapear linha de cliente: %w", err)
		}
		clientes = append(clientes, c)
	}

	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("erro pós-iteração de linhas: %w", err)
	}

	return clientes, nil
}

// Update atualiza o nome de um cliente existente.
func (r *ClienteRepositoryMysql) Update(ctx context.Context, cliente *entities.Cliente) error {
	query := "UPDATE clientes SET nome = ? WHERE id = ?"

	result, err := r.db.ExecContext(ctx, query, cliente.Nome, cliente.ID)
	if err != nil {
		return fmt.Errorf("erro ao atualizar cliente: %w", err)
	}

	rowsAffected, err := result.RowsAffected()
	if err != nil {
		return fmt.Errorf("erro ao checar linhas afetadas: %w", err)
	}

	if rowsAffected == 0 {
		return fmt.Errorf("cliente com ID %d não encontrado para atualização", cliente.ID)
	}

	return nil
}

// Delete remove um cliente pelo ID (ficará travado se houver venda vinculada devido ao RESTRICT).
func (r *ClienteRepositoryMysql) Delete(ctx context.Context, id int64) error {
	query := "DELETE FROM clientes WHERE id = ?"

	result, err := r.db.ExecContext(ctx, query, id)
	if err != nil {
		return fmt.Errorf("erro ao deletar cliente (verifique vínculos): %w", err)
	}

	rowsAffected, err := result.RowsAffected()
	if err != nil {
		return fmt.Errorf("erro ao checar linhas afetadas no delete: %w", err)
	}

	if rowsAffected == 0 {
		return fmt.Errorf("cliente com ID %d não encontrado para exclusão", id)
	}

	return nil
}
