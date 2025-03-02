# Install certs

```bash

chmod +x openssl_certs.sh && ./openssl_certs.sh

```
---

# Install deps

```bash

chmod +x update-deps.sh ./update-deps.sh

```

---

# Run Container

```bash

docker compose up -d --build

```

---

# Secrets

https://symfony.com/doc/current/configuration/env_var_processors.html

```yaml
#compose.yaml
services:
  php:
	#...
    secrets:
      - api_key
secrets:
  api_key:
    file: secrets/api_key.txt
```

In Symfony

```yaml
#api/config/services.yaml
parameters:
    app.api_key: '%env(file:API_KEY_FILE)%'
```

Call the env in php

```php
 $this->getParameter('app.api_key')
```


---
