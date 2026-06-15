package handlers

import (
	"net/http"
	"strconv"

	"github.com/bling-lwsa/devpool-base-web-api/internal/app/interfaces"
	"github.com/bling-lwsa/devpool-base-web-api/internal/app/messages"

	"github.com/gin-gonic/gin"
)

// ClienteHandler gerencia as requisições HTTP do domínio de Clientes.
type ClienteHandler struct {
	service interfaces.ClienteServiceInterface
}

// NewClienteHandler cria uma nova instância do handler de clientes.
func NewClienteHandler(service interfaces.ClienteServiceInterface) *ClienteHandler {
	return &ClienteHandler{service: service}
}

// Create lida com a criação de um novo cliente (POST /v1/clientes).
func (h *ClienteHandler) Create(c *gin.Context) {
	var input messages.CreateClienteInput
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "JSON inválido: " + err.Error()})
		return
	}

	output, err := h.service.Criar(c.Request.Context(), input)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusCreated, output)
}

// GetByID busca um cliente específico pelo ID (GET /v1/clientes/:id).
func (h *ClienteHandler) GetByID(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "ID inválido"})
		return
	}

	output, err := h.service.ObterPorID(c.Request.Context(), id)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	if output == nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Cliente não encontrado"})
		return
	}

	c.JSON(http.StatusOK, output)
}

// List retorna a lista paginada e filtrada de clientes (GET /v1/clientes).
func (h *ClienteHandler) List(c *gin.Context) {
	filtroNome := c.Query("q")
	if filtroNome == "" {
		filtroNome = c.Query("nome")
	}

	limitStr := c.DefaultQuery("limit", "10")
	offsetStr := c.DefaultQuery("offset", "0")

	limit, _ := strconv.Atoi(limitStr)
	offset, _ := strconv.Atoi(offsetStr)

	output, err := h.service.Listar(c.Request.Context(), filtroNome, limit, offset)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, output)
}

// Update atualiza um cliente existente (PUT /v1/clientes/:id).
func (h *ClienteHandler) Update(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "ID inválido"})
		return
	}

	var input messages.UpdateClienteInput
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "JSON inválido: " + err.Error()})
		return
	}

	output, err := h.service.Atualizar(c.Request.Context(), id, input)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	c.JSON(http.StatusOK, output)
}

// Delete remove um cliente (DELETE /v1/clientes/:id).
func (h *ClienteHandler) Delete(c *gin.Context) {
	idStr := c.Param("id")
	id, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "ID inválido"})
		return
	}

	err = h.service.Excluir(c.Request.Context(), id)
	if err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Não foi possível excluir o cliente. Verifique se ele possui vendas vinculadas."})
		return
	}

	// De acordo com o diagrama visual da banca, o retorno do DELETE deve ser 204 No Content
	c.Status(http.StatusNoContent)
}
