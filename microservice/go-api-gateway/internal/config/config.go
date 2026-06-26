package config

import (
	"os"
	"strings"

	"github.com/joho/godotenv"
)

type Config struct {
	AppPort            string
	AuthServiceURL     string
	ProductServiceURL  string
	ProductServiceURLs []string
	OrderServiceURL    string
	GatewaySecret      string
}

func Load() Config {
	_ = godotenv.Load()

	productServiceURL := os.Getenv("PRODUCT_SERVICE_URL")

	return Config{
		AppPort:            getEnv("APP_PORT", "4000"),
		AuthServiceURL:     os.Getenv("AUTH_SERVICE_URL"),
		ProductServiceURL:  productServiceURL,
		ProductServiceURLs: getEnvList("PRODUCT_SERVICE_URLS", productServiceURL),
		OrderServiceURL:    os.Getenv("ORDER_SERVICE_URL"),
		GatewaySecret:      os.Getenv("GATEWAY_SECRET"),
	}
}

func getEnv(key string, fallback string) string {
	value := os.Getenv(key)
	if value == "" {
		return fallback
	}

	return value
}

func getEnvList(key string, fallback string) []string {
	value := os.Getenv(key)
	if value == "" {
		value = fallback
	}

	parts := strings.Split(value, ",")
	items := make([]string, 0, len(parts))
	for _, part := range parts {
		item := strings.TrimSpace(part)
		if item != "" {
			items = append(items, strings.TrimRight(item, "/"))
		}
	}

	return items
}
