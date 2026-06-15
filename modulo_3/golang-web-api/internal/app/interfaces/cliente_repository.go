package interfaces

import (
	"context"

	"github.com/bling-lwsa/devpool-base-web-api/internal/app/messages"
)

type ClienteServiceInterface interface {
	Criar(ctx context.Context, input messages.CreateClienteInput) (*messages.ClienteOutput, error)
	ObterPorID(ctx context.Context, id int64) (*messages.ClienteOutput, error)
	Listar(ctx context.Context, filtroNome string, limit, offset int) ([]messages.ClienteOutput, error)
	Atualizar(ctx context.Context, id int64, input messages.UpdateClienteInput) (*messages.ClienteOutput, error)
	Excluir(ctx context.Context, id int64) error
}
