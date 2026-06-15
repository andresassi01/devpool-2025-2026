package mocks

import (
	"context"

	"github.com/bling-lwsa/devpool-base-web-api/internal/domain/entities"
)

type ClienteRepositoryMock struct {
	CreateFn  func(ctx context.Context, c *entities.Cliente) error
	GetByIDFn func(ctx context.Context, id int64) (*entities.Cliente, error)
	ListFn    func(ctx context.Context, nome string, limit, offset int) ([]entities.Cliente, error)
	UpdateFn  func(ctx context.Context, c *entities.Cliente) error
	DeleteFn  func(ctx context.Context, id int64) error
}

func (m *ClienteRepositoryMock) Create(ctx context.Context, c *entities.Cliente) error {
	return m.CreateFn(ctx, c)
}

func (m *ClienteRepositoryMock) GetByID(ctx context.Context, id int64) (*entities.Cliente, error) {
	return m.GetByIDFn(ctx, id)
}

func (m *ClienteRepositoryMock) List(ctx context.Context, nome string, limit, offset int) ([]entities.Cliente, error) {
	return m.ListFn(ctx, nome, limit, offset)
}

func (m *ClienteRepositoryMock) Update(ctx context.Context, c *entities.Cliente) error {
	return m.UpdateFn(ctx, c)
}

func (m *ClienteRepositoryMock) Delete(ctx context.Context, id int64) error {
	return m.DeleteFn(ctx, id)
}
