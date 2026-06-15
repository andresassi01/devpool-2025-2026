package interfaces

import (
	"context"

	"github.com/bling-lwsa/devpool-base-web-api/internal/domain/entities"
)

// ClienteRepository define o contrato para persistência de dados de Clientes.
type ClienteRepository interface {
	Create(ctx context.Context, cliente *entities.Cliente) error
	GetByID(ctx context.Context, id int64) (*entities.Cliente, error)
	List(ctx context.Context, filtroNome string, limit, offset int) ([]entities.Cliente, error)
	Update(ctx context.Context, cliente *entities.Cliente) error
	Delete(ctx context.Context, id int64) error
}
