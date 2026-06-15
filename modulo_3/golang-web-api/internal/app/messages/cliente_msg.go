package messages

// CreateClienteInput representa os dados recebidos para criar um cliente.
type CreateClienteInput struct {
	Nome string `json:"nome"`
}

// UpdateClienteInput representa os dados recebidos para atualizar um cliente.
type UpdateClienteInput struct {
	Nome string `json:"nome"`
}

// ClienteOutput representa o formato padrão de retorno de um cliente para a API.
type ClienteOutput struct {
	ID   int64  `json:"id"`
	Nome string `json:"nome"`
}
