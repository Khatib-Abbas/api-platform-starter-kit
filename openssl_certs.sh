#!/bin/sh

# Create directory for certificates
# This ensures we have a dedicated location for our SSL certificates
mkdir -p certs
cd certs

# Create temporary SAN (Subject Alternative Names) configuration file
# This file contains all the certificate settings and extensions
cat > san.cnf << EOF
[req]
distinguished_name = req_distinguished_name  # Specify the DN section to use
req_extensions = v3_req                     # Specify which extensions to add to the cert request
prompt = no                                 # Disable interactive prompting

# Certificate subject information
[req_distinguished_name]
C = BE                                      # Country
ST = Bruxelles                             # State/Province
L = Bruxelles                              # Locality
O = EEAS                                   # Organization
OU = IT                                    # Organizational Unit
CN = database                              # Common Name (server hostname)
emailAddress = eeas.no-send@ext.eeas.europa.eu

# Certificate extensions for the server certificate
[v3_req]
basicConstraints = CA:FALSE                 # Indicate this is NOT a CA certificate
keyUsage = nonRepudiation, digitalSignature, keyEncipherment  # Define allowed key uses
subjectAltName = @alt_names                # Enable alternative DNS names

# Define Subject Alternative Names (SAN)
# Multiple DNS names that the certificate is valid for
[alt_names]
DNS.1 = database                           # Primary DNS name
DNS.2 = localhost                          # Alternative DNS name
EOF

# Generate CA (Certificate Authority) private key
# 2048 bits RSA key - this is the root key that signs other certificates
openssl genrsa 2048 > ca-key.pem

# Create self-signed CA certificate
# This creates a root certificate valid for 10 years (3650 days)
openssl req -new -x509 -nodes -days 3650 -key ca-key.pem -out ca-cert.pem \
  -config san.cnf

# Generate server's private key
# 2048 bits RSA key - this is the key used by the database server
openssl genrsa 2048 > server-key.pem

# Create Certificate Signing Request (CSR) for the server
# This generates a request to be signed by the CA
openssl req -new -key server-key.pem -out server-req.pem \
  -config san.cnf

# Sign the server's CSR with the CA certificate
# This creates the final server certificate, valid for 10 years
openssl x509 -req -in server-req.pem -days 3650 \
  -CA ca-cert.pem -CAkey ca-key.pem -set_serial 01 \
  -out server-cert.pem \
  -extfile san.cnf -extensions v3_req

# Verify the certificate
# Display the DNS names included in the certificate for verification
echo "\nVerifying DNS names in the certificate:"
openssl x509 -in server-cert.pem -text -noout | grep DNS:

# Clean up temporary configuration file
rm san.cnf

# Display success message and list generated files
echo "\nCertificates successfully generated in certs/ directory"
ls -l

# Generated files explanation:
# - ca-key.pem:     Private key of the Certificate Authority
# - ca-cert.pem:    Public certificate of the Certificate Authority
# - server-key.pem: Private key of the database server
# - server-cert.pem: Public certificate of the database server
# - server-req.pem: Certificate signing request (temporary file)
