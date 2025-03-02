mkcert -key-file server.key -cert-file server.crt -ecdsa=false localhost postgres db
cp "$(mkcert -CAROOT)/rootCA.pem" ./certs/ca.pem
chmod 644 certs/ca.pem
chmod 644 certs/server.crt
chmod 600 certs/server.key
