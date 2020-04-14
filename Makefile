# -- Docker
COMPOSE              = docker-compose
COMPOSE_RUN          = $(COMPOSE) run --rm

# ==============================================================================
# RULES

default: help

bootstrap: ## Prepare Docker images for the project and install Nextcloud
bootstrap: \
	build \
	env.d/sync \
	install
.PHONY: bootstrap

build: ## Build the nextcloud and sync images
	@$(COMPOSE) build nextcloud
	@$(COMPOSE) build sync
.PHONY: build

env.d/sync:
	cp env.d/sync.dist env.d/sync

install: ## Install Nextcloud instance
	@$(COMPOSE) up -d db
	@echo "Wait for postgresql to be up..."
	@$(COMPOSE_RUN) dockerize -wait tcp://db:5432 -timeout 60s
	@$(COMPOSE_RUN) --entrypoint install.sh nextcloud-install
.phony: install

run: ## Start the development nextcloud server using Docker
	@$(COMPOSE) up -d db
	@echo "Wait for postgresql to be up..."
	@$(COMPOSE_RUN) dockerize -wait tcp://db:5432 -timeout 60s
	@$(COMPOSE) up -d nextcloud
.PHONY: run

sync: ## Run the sync script
	@$(COMPOSE) up -d db
	@echo "Wait for postgresql to be up..."
	@$(COMPOSE_RUN) sync
.PHONY: sync

stop: ## Stop the development nextcloud server using Docker
	@$(COMPOSE) stop
.PHONY: stop

# -- Misc

help:
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
.PHONY: help
