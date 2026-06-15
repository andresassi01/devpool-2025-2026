package services_test

import (
	"context"
	"testing"

	"github.com/bling-lwsa/devpool-base-web-api/internal/app/messages"
	"github.com/bling-lwsa/devpool-base-web-api/internal/app/mocks"
	"github.com/bling-lwsa/devpool-base-web-api/internal/app/services"
	"github.com/bling-lwsa/devpool-base-web-api/internal/domain/entities"
	"github.com/stretchr/testify/assert"
)

func TestClienteService_Criar(t *testing.T) {
	tests := []struct {
		name    string
		input   messages.CreateClienteInput
		mockFn  func(ctx context.Context, c *entities.Cliente) error
		wantErr bool
	}{
		{
			name:  "sucesso",
			input: messages.CreateClienteInput{Nome: "Cliente Teste"},
			mockFn: func(_ context.Context, c *entities.Cliente) error {
				c.ID = 1
				return nil
			},
			wantErr: false,
		},
		{
			name:    "erro de validacao (nome vazio)",
			input:   messages.CreateClienteInput{Nome: ""},
			mockFn:  func(_ context.Context, _ *entities.Cliente) error { return nil },
			wantErr: true,
		},
	}

	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			repo := &mocks.ClienteRepositoryMock{CreateFn: tt.mockFn}
			svc := services.NewClienteService(repo)

			result, err := svc.Criar(context.Background(), tt.input)

			if tt.wantErr {
				assert.Error(t, err)
			} else {
				assert.NoError(t, err)
				assert.Equal(t, tt.input.Nome, result.Nome)
			}
		})
	}
}

func TestClienteService_ObterPorID(t *testing.T) {
	t.Run("sucesso ao buscar", func(t *testing.T) {
		repo := &mocks.ClienteRepositoryMock{
			GetByIDFn: func(ctx context.Context, id int64) (*entities.Cliente, error) {
				return &entities.Cliente{ID: 1, Nome: "Teste"}, nil
			},
		}
		svc := services.NewClienteService(repo)

		result, err := svc.ObterPorID(context.Background(), 1)

		assert.NoError(t, err)
		assert.NotNil(t, result)
		assert.Equal(t, "Teste", result.Nome)
	})

	t.Run("cliente nao encontrado", func(t *testing.T) {
		repo := &mocks.ClienteRepositoryMock{
			GetByIDFn: func(ctx context.Context, id int64) (*entities.Cliente, error) {
				return nil, nil // Simula o caso de não encontrar (err=nil, cliente=nil)
			},
		}
		svc := services.NewClienteService(repo)

		result, err := svc.ObterPorID(context.Background(), 999)

		assert.NoError(t, err)
		assert.Nil(t, result)
	})
}
