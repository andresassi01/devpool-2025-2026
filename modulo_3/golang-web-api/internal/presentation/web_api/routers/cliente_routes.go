package routers

import (
	"github.com/bling-lwsa/devpool-base-web-api/internal/presentation/web_api/handlers"
	"github.com/gin-gonic/gin"
)

// MapClienteRoutes registra os endpoints do CRUD de clientes no grupo v1 do Gin.
func MapClienteRoutes(router *gin.RouterGroup, handler *handlers.ClienteHandler) {
	clientes := router.Group("/clientes")
	{
		clientes.POST("", handler.Create)
		clientes.GET("/:id", handler.GetByID)
		clientes.GET("", handler.List)
		clientes.PUT("/:id", handler.Update)
		clientes.DELETE("/:id", handler.Delete)
	}
}
