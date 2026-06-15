package entities

import (
	"errors"
	"strings"
)

// Cliente representa a entidade de domínio do cadastro de clientes.
type Cliente struct {
	ID   int64  `json:"id"`
	Nome string `json:"nome"`
}

// Validar verifica as regras de negócio do cliente exigidas pela banca.
func (c *Cliente) Validar() error {
	c.Nome = strings.TrimSpace(c.Nome)

	if c.Nome == "" {
		return errors.New("o nome do cliente é obrigatório")
	}

	// Requisito do diagrama do banco de dados
	if len(c.Nome) > 255 {
		return errors.New("o nome do cliente não pode exceder 255 caracteres")
	}

	return nil
}
