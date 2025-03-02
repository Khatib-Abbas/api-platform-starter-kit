
---

# Traefik HTTPS Architecture with Caddy and PHP

This sequence diagram illustrates the architecture and communication flow of a Docker-based development environment using Traefik as a reverse proxy, with TLS termination and HTTPS communication to a PHP service running Caddy.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant U as User
    participant T as Traefik (443)
    participant P as PHP Service (8443)
    participant C as Caddy (8443)
    participant M as mkcert
    
    Note over U,M: System Initialization
    M->>M: Check if certificates exist
    alt Certificates missing
        M->>M: Generate TLS certificates
        M->>M: Save to ./certs/website_certs/
    else Certificates exist
        M->>M: No regeneration needed
    end
    
    Note over U,T: Services Startup
    M-->>T: Access to certificates via volume
    M-->>P: Access to certificates via volume
    
    Note over U,C: Request Flow
    U->>T: HTTPS request to php.docker.localhost
    T->>T: Check routing rules 
    T->>T: TLS termination with certificates
    T->>P: Redirect HTTPS to PHP service on port 8443
    P->>C: Caddy handles request inside PHP container
    C->>C: Uses local certificates for HTTPS
    C->>C: Process PHP request
    C-->>P: Response
    P-->>T: Response
    T-->>U: Final response to user
```

## Architecture Details

### Certificate Management:

The mkcert service checks for and generates TLS certificates for docker.localhost and *.docker.localhost
Certificates are stored in ./certs/website_certs/ and shared via Docker volumes


### Service Configuration:

Traefik: Acts as the main reverse proxy, listening on ports 80 and 443
PHP Service: Exposes port 8443 internally for HTTPS
Caddy: Functions as the web server within the PHP container on port 8443


Request Flow:

External requests come in through Traefik on port 443
Traefik routes requests to the appropriate service based on hostname
For php.docker.localhost, Traefik forwards to the PHP service on port 8443
The PHP service uses Caddy to handle the request and process PHP code
Responses flow back through the same path



This end-to-end HTTPS setup provides a secure local development environment with self-signed certificates.
