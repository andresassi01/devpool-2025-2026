package mappers

import (
	"github.com/bling-lwsa/devpool-base-web-api/internal/app/messages"
	"github.com/bling-lwsa/devpool-base-web-api/internal/domain/entities"
)

func ToOutput(e *entities.Cliente) *messages.ClienteOutput {
	return &messages.ClienteOutput{
		ID:   e.ID,
		Nome: e.Nome,
	}
}

func ToOutputList(entitiesList []entities.Cliente) []messages.ClienteOutput {
	output := make([]messages.ClienteOutput, 0, len(entitiesList))

	for _, e := range entitiesList {
		tempEntity := e
		output = append(output, *ToOutput(&tempEntity))
	}
	return output
}
