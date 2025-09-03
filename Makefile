local:
        frankenphp run --config frankenphp/Caddyfile

prune:
	git gc --aggressive --prune
