# Docker Replicas and Traefik Load Balancing

This document explains how Docker manages service replicas and how Traefik performs load balancing across these replicas.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant User
    participant Traefik as Traefik Reverse Proxy
    participant Docker as Docker Engine
    participant S1 as PHP Service<br/>Replica 1 (php.1)
    participant S2 as PHP Service<br/>Replica 2 (php.2)
    participant DB as Database

    Note over Docker,S2: Initialization Phase
    Docker->>Docker: Parse docker-compose.yml
    Docker->>Docker: Process 'deploy' config<br/>(replicas: 2, mode: replicated)
    Docker->>Docker: Check 'endpoint_mode: vip'<br/>(Virtual IP for service)
    
    Docker->>S1: Create php service replica 1<br/>(auto-named: php.1.xyz123)
    Docker->>S2: Create php service replica 2<br/>(auto-named: php.2.abc456)
    
    Note right of Docker: Cannot use 'container_name'<br/>with replicas since Docker<br/>needs to generate unique<br/>container names
    
    S1->>DB: Wait for DB to be healthy
    S2->>DB: Wait for DB to be healthy
    
    Docker->>Docker: Create Virtual IP (VIP)<br/>for php service
    Docker->>Traefik: Register service endpoint<br/>with single VIP
    
    Note over Traefik,Docker: Traefik Discovery Phase
    Traefik->>Docker: Discover services via Docker API
    Docker->>Traefik: Return service metadata<br/>(including container replicas)
    Traefik->>Traefik: Update internal routing table<br/>with backend replicas

    Note over User,DB: Request Processing Phase
    User->>Traefik: HTTP Request to php.docker.localhost
    Traefik->>Traefik: Apply routing rules
    
    alt Round Robin Load Balancing (default)
        Traefik->>S1: Forward request to Replica 1
        S1->>DB: Database query
        DB->>S1: Query result
        S1->>Traefik: HTTP Response
        Traefik->>User: Forward response to user
        
        Note over Traefik,S2: Next Request
        User->>Traefik: Another HTTP Request
        Traefik->>S2: Forward to Replica 2<br/>(round robin)
        S2->>DB: Database query
        DB->>S2: Query result
        S2->>Traefik: HTTP Response
        Traefik->>User: Forward response to user
    end
    
    Note over User,DB: Health Check Phase
    Traefik->>S1: Health check request
    Traefik->>S2: Health check request
    
    Note over Docker,S2: Scale Change Phase
    Docker->>Docker: Scale service to 3 replicas
    Docker->>Docker: Create PHP Service Replica 3
    Docker->>Traefik: Update service discovery
    Traefik->>Traefik: Update load balancing pool
```

## Key Concepts Explained

### Docker Replicas Configuration

1. **Service Replication**:
    - The `deploy.replicas` setting in docker-compose.yml specifies how many instances of a service to run
    - `mode: replicated` tells Docker to maintain the specified number of replicas
    - Docker automatically generates unique names for each replica (e.g., php.1.xyz123, php.2.abc456)

2. **Container Name Restriction**:
    - You cannot use `container_name` with replicated services
    - Docker needs to generate unique container names for each replica
    - Format is typically: `service_name.replica_number.container_id_short`

3. **Endpoint Mode**:
    - `endpoint_mode: vip` (Virtual IP) creates a single virtual IP for the service
    - Clients connect to the VIP, and Docker's internal routing handles directing to available replicas
    - Alternative is `dnsrr` (DNS Round Robin) for DNS-based load balancing

### Traefik Load Balancing

1. **Service Discovery**:
    - Traefik connects to Docker's API to discover services and their replicas
    - It automatically detects when replicas are added or removed
    - Services are registered with Traefik either through Docker labels or dynamic configuration

2. **Load Balancing Methods**:
    - Default method is Round Robin (sends requests in rotation to each replica)
    - Traefik supports other methods:
        - `wrr`: Weighted Round Robin
        - `sticky`: Session affinity (same client always goes to same replica)

3. **Health Checks**:
    - Traefik periodically checks replica health
    - Unhealthy replicas are removed from the load balancing pool
    - When a replica returns to healthy status, it rejoins the pool

4. **Scale Changes**:
    - When replicas are added or removed, Docker notifies Traefik
    - Traefik automatically updates its routing tables
    - No configuration changes needed for dynamic scaling

This architecture enables horizontal scaling and high availability without requiring manual intervention for routing configuration.
