package services

import (
	"context"

	"github.com/bling-lwsa/devpool-base-web-api/internal/app/mappers"
	"github.com/bling-lwsa/devpool-base-web-api/internal/app/messages"
	"github.com/bling-lwsa/devpool-base-web-api/internal/domain/entities"

	appInterfaces "github.com/bling-lwsa/devpool-base-web-api/internal/app/interfaces"
	domainInterfaces "github.com/bling-lwsa/devpool-base-web-api/internal/domain/interfaces"
)

var _ appInterfaces.ClienteServiceInterface = (*ClienteService)(nil)

// ClienteService orquestra as operações de negócio para clientes.
type ClienteService struct {
	repo domainInterfaces.ClienteRepository
}

// NewClienteService cria uma nova instância do serviço de clientes.
func NewClienteService(repo domainInterfaces.ClienteRepository) *ClienteService {
	return &ClienteService{repo: repo}
}

// Criar valida e persiste um novo cliente.
func (s *ClienteService) Criar(ctx context.Context, input messages.CreateClienteInput) (*messages.ClienteOutput, error) {
	cliente := &entities.Cliente{
		Nome: input.Nome,
	}

	if err := cliente.Validar(); err != nil {
		return nil, err
	}

	if err := s.repo.Create(ctx, cliente); err != nil {
		return nil, err
	}

	return mappers.ToOutput(cliente), nil
}

// ObterPorID busca um cliente pelo identificador.
func (s *ClienteService) ObterPorID(ctx context.Context, id int64) (*messages.ClienteOutput, error) {
	cliente, err := s.repo.GetByID(ctx, id)
	if err != nil {
		return nil, err
	}
	if cliente == nil {
		return nil, nil
	}

	return mappers.ToOutput(cliente), nil
}

// Listar busca os clientes aplicando filtros e paginação.
func (s *ClienteService) Listar(ctx context.Context, filtroNome string, limit, offset int) ([]messages.ClienteOutput, error) {
	if limit <= 0 {
		limit = 10
	}

	clientes, err := s.repo.List(ctx, filtroNome, limit, offset)
	if err != nil {
		return nil, err
	}

	// Veja como fica limpo e padronizado:
	return mappers.ToOutputList(clientes), nil
}

// Atualizar modifica o cadastro de um cliente existente.
func (s *ClienteService) Atualizar(ctx context.Context, id int64, input messages.UpdateClienteInput) (*messages.ClienteOutput, error) {
	cliente := &entities.Cliente{
		ID:   id,
		Nome: input.Nome,
	}

	if err := cliente.Validar(); err != nil {
		return nil, err
	}

	if err := s.repo.Update(ctx, cliente); err != nil {
		return nil, err
	}

	return mappers.ToOutput(cliente), nil
}

// Excluir remove o registro de um cliente do sistema.
func (s *ClienteService) Excluir(ctx context.Context, id int64) error {
	return s.repo.Delete(ctx, id)
}
