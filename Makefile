.PHONY: list

include .env

PHP_CONTAINER := $(COMPOSE_PROJECT_NAME)-$(PHP_CONTAINER_NAME)
NGINX_CONTAINER := $(COMPOSE_PROJECT_NAME)-$(NGINX_CONTAINER_NAME)
DB_CONTAINER := $(COMPOSE_PROJECT_NAME)-$(DB_CONTAINER_NAME)

list:
	@LC_ALL=C $(MAKE) -pRrq -f $(firstword $(MAKEFILE_LIST)) : 2>/dev/null | awk -v RS= -F: '/(^|\n)# Files(\n|$$)/,/(^|\n)# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | grep -E -v -e '^[^[:alnum:]]' -e '^$@$$'
# IMPORTANT: The line above must be indented by (at least one)
#            *actual TAB character* - *spaces* do *not* work.

composer_inst:
	docker exec -t $(PHP_CONTAINER) sh -c 'cd /var/www/html && composer install'
install:
	docker compose up -d --build
start:
	docker start $(PHP_CONTAINER) $(NGINX_CONTAINER) $(DB_CONTAINER)
stop:
	docker stop $(PHP_CONTAINER) $(NGINX_CONTAINER) $(DB_CONTAINER)
terminal_php:
	docker exec -it $(PHP_CONTAINER) sh
terminal_nginx:
	docker exec -it $(NGINX_CONTAINER) sh